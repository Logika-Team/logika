import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { existsSync, mkdtempSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import test from 'node:test';
import { fileURLToPath } from 'node:url';
import sharp from 'sharp';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const scriptPath = join(root, 'scripts/media/convert-png-to-webp.mjs');
const manifestPath = join(root, 'scripts/media/webp-manifest.json');

function run(args) {
  return execFileSync(process.execPath, [scriptPath, ...args], { cwd: root, encoding: 'utf8' });
}

function runExpectFailure(args) {
  try {
    run(args);
    return { status: 0, output: '' };
  } catch (error) {
    return { status: error.status, output: `${error.stdout ?? ''}${error.stderr ?? ''}` };
  }
}

test('convert-png-to-webp: converts, is idempotent, detects staleness, cleans up after itself', async () => {
  const fixtureDir = mkdtempSync(join(tmpdir(), 'logika-webp-fixture-'));
  const manifestBackup = existsSync(manifestPath) ? readFileSync(manifestPath, 'utf8') : null;

  try {
    const pngPath = join(fixtureDir, 'sample.png');
    await sharp({
      create: { width: 20, height: 20, channels: 4, background: { r: 10, g: 20, b: 30, alpha: 0.5 } },
    })
      .png()
      .toFile(pngPath);

    // Nothing converted yet: --check must fail.
    const before = runExpectFailure(['--check', `--dir=${fixtureDir}`]);
    assert.equal(before.status, 1);
    assert.match(before.output, /sample\.png/);

    // Convert: webp sibling appears.
    run([`--dir=${fixtureDir}`]);
    const webpPath = join(fixtureDir, 'sample.webp');
    assert.ok(existsSync(webpPath), 'expected sample.webp to be created');

    // --check now passes.
    const afterCheckOutput = run(['--check', `--dir=${fixtureDir}`]);
    assert.match(afterCheckOutput, /OK/);

    // Re-running without changes does no work (idempotent).
    const rerunOutput = run([`--dir=${fixtureDir}`]);
    assert.match(rerunOutput, /Nothing to do/);

    // Changing the source content makes it stale again.
    await sharp({
      create: { width: 20, height: 20, channels: 4, background: { r: 200, g: 100, b: 50, alpha: 0.5 } },
    })
      .png()
      .toFile(pngPath);
    const staleCheck = runExpectFailure(['--check', `--dir=${fixtureDir}`]);
    assert.equal(staleCheck.status, 1);
  } finally {
    rmSync(fixtureDir, { recursive: true, force: true });
    if (manifestBackup === null) {
      rmSync(manifestPath, { force: true });
    } else {
      writeFileSync(manifestPath, manifestBackup);
    }
  }
});
