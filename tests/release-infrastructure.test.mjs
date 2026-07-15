import assert from 'node:assert/strict';
import { execFileSync, execSync } from 'node:child_process';
import { mkdtempSync, readFileSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import test from 'node:test';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');

function run(command, args, options = {}) {
  return execFileSync(command, args, {
    cwd: root,
    encoding: 'utf8',
    ...options,
  });
}

test('build artifact contains only managed WordPress components and release metadata', () => {
  const outputDir = mkdtempSync(join(tmpdir(), 'logika-release-test-'));

  try {
    const artifactPath = run('./scripts/release/build-artifact.sh', [
      '--source-root', root,
      '--output-dir', outputDir,
    ]).trim();
    const manifest = JSON.parse(readFileSync(join(outputDir, 'release-manifest.json'), 'utf8'));
    const archiveEntries = execFileSync('tar', ['-tzf', artifactPath], { encoding: 'utf8' })
      .trim()
      .split('\n')
      .filter(Boolean);

    assert.equal(manifest.commitSha, run('git', ['rev-parse', 'HEAD']).trim());
    assert.equal(manifest.migrations.required, false);
    assert.deepEqual(manifest.components, [
      'wordpress/wp-content/themes/logika-theme',
      'wordpress/wp-content/plugins/logika-core',
      'wordpress/wp-content/plugins/logika-leads',
    ]);
    assert.ok(archiveEntries.includes('release-manifest.json'));
    assert.ok(archiveEntries.some((entry) => entry.startsWith('wordpress/wp-content/themes/logika-theme/')));
    assert.ok(archiveEntries.some((entry) => entry.startsWith('wordpress/wp-content/plugins/logika-core/')));
    assert.ok(archiveEntries.some((entry) => entry.startsWith('wordpress/wp-content/plugins/logika-leads/')));
    assert.ok(archiveEntries.every((entry) => (
      entry === 'release-manifest.json'
      || entry === 'wordpress/'
      || entry === 'wordpress/wp-content/'
      || entry === 'wordpress/wp-content/themes/'
      || entry === 'wordpress/wp-content/plugins/'
      || entry.startsWith('wordpress/wp-content/themes/logika-theme/')
      || entry.startsWith('wordpress/wp-content/plugins/logika-core/')
      || entry.startsWith('wordpress/wp-content/plugins/logika-leads/')
    )));
  } finally {
    rmSync(outputDir, { recursive: true, force: true });
  }
});

test('deploy refuses to connect until every required target parameter is supplied', () => {
  assert.throws(
    () => run('./scripts/release/deploy.sh', ['--artifact', '/tmp/release.tar.gz'], {
      env: { PATH: process.env.PATH },
      stdio: ['ignore', 'pipe', 'pipe'],
    }),
    /DEPLOY_HOST is required/,
  );
});

test('deploy rejects traversal paths in an archive before opening SSH', () => {
  const outputDir = mkdtempSync(join(tmpdir(), 'logika-release-traversal-test-'));
  const archivePath = join(outputDir, 'unsafe.tar.gz');

  try {
    execFileSync('python3', ['-c', `
import io
import tarfile
import sys

with tarfile.open(sys.argv[1], 'w:gz') as archive:
    entry = tarfile.TarInfo('wordpress/wp-content/plugins/logika-core/../../wp-config.php')
    payload = b'unsafe'
    entry.size = len(payload)
    archive.addfile(entry, io.BytesIO(payload))
`, archivePath]);

    assert.throws(
      () => run('./scripts/release/deploy.sh', ['--artifact', archivePath], {
        env: {
          ...process.env,
          DEPLOY_HOST: 'invalid.example.test',
          DEPLOY_USER: 'deploy',
          DEPLOY_ROOT: '/srv/logika',
          DEPLOY_SITE_ROOT: '/var/www/logika',
        },
        stdio: ['ignore', 'pipe', 'pipe'],
      }),
      /unsafe path/,
    );
  } finally {
    rmSync(outputDir, { recursive: true, force: true });
  }
});

test('release workflows enforce staged approval and document the readiness boundary', () => {
  const stagingWorkflow = readFileSync(join(root, '.github/workflows/deploy-staging.yml'), 'utf8');
  const productionWorkflow = readFileSync(join(root, '.github/workflows/deploy-production.yml'), 'utf8');
  const validationWorkflow = readFileSync(join(root, '.github/workflows/validate.yml'), 'utf8');
  const deploymentGuide = readFileSync(join(root, 'docs/guidelines/deployment.md'), 'utf8');

  assert.match(stagingWorkflow, /branches:\s*\n\s*- wordpress/);
  assert.match(stagingWorkflow, /environment:\s*\n\s*name: staging/);
  assert.match(productionWorkflow, /workflow_dispatch:/);
  assert.match(productionWorkflow, /ref: wordpress/);
  assert.match(productionWorkflow, /environment:\s*\n\s*name: production/);
  assert.match(productionWorkflow, /staging_run_id/);
  assert.match(validationWorkflow, /npm ci/);
  assert.match(validationWorkflow, /npm run build/);
  assert.match(validationWorkflow, /npm run html/);
  assert.match(validationWorkflow, /scripts\/release\/run-wordpress-tests\.sh/);
  assert.match(validationWorkflow, /ghcr\.io\/gitleaks\/gitleaks:v8\.24\.2/);
  assert.doesNotMatch(validationWorkflow, /gitleaks\/gitleaks-action/);
  const wordpressRunner = readFileSync(join(root, 'scripts/release/run-wordpress-tests.sh'), 'utf8');
  assert.match(wordpressRunner, /ddev exec scripts\/release\/prepare-wordpress-tests\.sh/);
  assert.match(wordpressRunner, /wp eval-file --path=wordpress scripts\/seed-cities\.php/);
  assert.match(readFileSync(join(root, 'scripts/release/prepare-wordpress-tests.sh'), 'utf8'), /wp core install/);
  assert.match(deploymentGuide, /## Infrastructure release readiness/);
  assert.match(deploymentGuide, /## Application go-live readiness/);
});

test('release scripts support host-specific WP-CLI command secrets', () => {
  const stagingWorkflow = readFileSync(join(root, '.github/workflows/deploy-staging.yml'), 'utf8');
  const productionWorkflow = readFileSync(join(root, '.github/workflows/deploy-production.yml'), 'utf8');

  for (const workflow of [stagingWorkflow, productionWorkflow]) {
    assert.match(workflow, /WP_CLI_BIN: \$\{\{ secrets\.WP_CLI_BIN \}\}/);
  }

  for (const script of ['deploy.sh', 'preflight.sh', 'backup.sh']) {
    const source = readFileSync(join(root, 'scripts/release', script), 'utf8');
    assert.match(source, /wp_cli\(\) \{/);
    assert.doesNotMatch(source, /"\$WP_CLI_BIN" --path=/);
  }

  const deployScript = readFileSync(join(root, 'scripts/release/deploy.sh'), 'utf8');
  assert.match(deployScript, /wp_cli --path="\$DEPLOY_SITE_ROOT" theme activate logika-theme/);
  assert.match(deployScript, /wp_cli --path="\$DEPLOY_SITE_ROOT" plugin activate logika-core logika-leads/);

  const preflightScript = readFileSync(join(root, 'scripts/release/preflight.sh'), 'utf8');
  assert.match(preflightScript, /rest_get_server\(\)->get_routes\(\)/);
  assert.match(preflightScript, /\/logika\/v1\/phone-country/);
  assert.match(preflightScript, /no-store/);
  assert.doesNotMatch(preflightScript, /rest route list/);
});

test('WordPress integration bootstrap seeds baseline homepage ACF content', () => {
  const prepareScript = readFileSync(join(root, 'scripts/release/prepare-wordpress-tests.sh'), 'utf8');
  const frontPagePosition = prepareScript.indexOf('wp option update --path=wordpress page_on_front "$home_id"');
  const seedPosition = prepareScript.indexOf('wp eval-file --path=wordpress scripts/seed-home-texts.php');
  const cachePosition = prepareScript.indexOf('wp cache flush --path=wordpress');

  assert.notEqual(frontPagePosition, -1);
  assert.notEqual(seedPosition, -1);
  assert.ok(
    seedPosition > frontPagePosition,
    'homepage ACF seed must run after page_on_front is configured',
  );
  assert.ok(
    seedPosition < cachePosition,
    'homepage ACF seed should run before the final cache flush',
  );
});

test('WordPress integration bootstrap creates managed editor pages', () => {
  const prepareScript = readFileSync(join(root, 'scripts/release/prepare-wordpress-tests.sh'), 'utf8');

  for (const slug of ['about', 'faq', 'it-courses', 'english-courses', 'media-center']) {
    assert.match(prepareScript, new RegExp(`${slug}:`));
  }

  assert.match(prepareScript, /wp post create --path=wordpress --post_type=page/);
});

test('staging smoke checks public homepage without REST rate-limit coupling', () => {
  const smokeScript = readFileSync(join(root, 'scripts/release/smoke.sh'), 'utf8');

  assert.match(smokeScript, /data-logika-lead-form/);
  assert.match(smokeScript, /noindex/);
  assert.match(smokeScript, /--retry 2/);
  assert.doesNotMatch(smokeScript, /wp-json/);
  assert.doesNotMatch(smokeScript, /rest_route/);
  assert.doesNotMatch(smokeScript, /--head/);
});

test('staging database sync stays staging-only and never changes production', () => {
  const stagingWorkflow = readFileSync(join(root, '.github/workflows/deploy-staging.yml'), 'utf8');
  const productionWorkflow = readFileSync(join(root, '.github/workflows/deploy-production.yml'), 'utf8');
  const exportScript = readFileSync(join(root, 'scripts/release/export-local-staging-db.sh'), 'utf8');
  const importScript = readFileSync(join(root, 'scripts/release/import-staging-db.sh'), 'utf8');

  assert.match(stagingWorkflow, /Import pending staging database dump/);
  assert.match(stagingWorkflow, /scripts\/release\/import-staging-db\.sh/);
  assert.doesNotMatch(productionWorkflow, /import-staging-db/);
  assert.match(exportScript, /search-replace/);
  assert.match(exportScript, /--export="\$sql_path"/);
  assert.match(exportScript, /staging\.logika\.resumemyhost\.miy\.link/);
  assert.match(exportScript, /production_url=/);
  assert.match(exportScript, /Exported dump contains the production URL/);
  assert.match(importScript, /incoming\/staging-db\.sql\.gz/);
  assert.match(importScript, /db import/);
  assert.match(importScript, /backup_dir=/);
  assert.match(importScript, /No pending staging database dump/);
});
