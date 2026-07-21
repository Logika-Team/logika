#!/usr/bin/env node
// Converts PNG/JPEG images under the configured target directories to WebP siblings.
// Usage:
//   node scripts/media/convert-png-to-webp.mjs                 convert everything that's missing/stale
//   node scripts/media/convert-png-to-webp.mjs --check          exit 1 if any source lacks an up-to-date webp
//   node scripts/media/convert-png-to-webp.mjs --force          reconvert even if the manifest looks up to date
//   node scripts/media/convert-png-to-webp.mjs --dir=path/to    restrict to one directory (repeatable)
//
// Staleness is tracked by content hash in webp-manifest.json (not mtime, which git checkout
// resets on every file uniformly and can't be trusted to reflect "did the source change").

import { readFileSync, existsSync, mkdirSync } from 'node:fs';
import { readFile, writeFile, stat } from 'node:fs/promises';
import { createHash } from 'node:crypto';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import fastGlob from 'fast-glob';
import sharp from 'sharp';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, '..', '..');
const manifestPath = path.join(__dirname, 'webp-manifest.json');

const args = process.argv.slice(2);
const checkOnly = args.includes('--check');
const force = args.includes('--force');
const dirArgs = args.filter((a) => a.startsWith('--dir=')).map((a) => a.slice('--dir='.length));

const PNG_QUALITY = { quality: 90, alphaQuality: 100 };
const LOSSY_QUALITY = { quality: 82 };

function loadTargets() {
	const configPath = path.join(__dirname, 'webp-targets.json');
	const config = JSON.parse(readFileSync(configPath, 'utf8'));
	const directories = dirArgs.length > 0 ? dirArgs : config.directories;
	return { directories, extensions: config.extensions };
}

function loadManifest() {
	if (!existsSync(manifestPath)) return {};
	return JSON.parse(readFileSync(manifestPath, 'utf8'));
}

async function saveManifest(manifest) {
	const sorted = Object.fromEntries(Object.entries(manifest).sort(([a], [b]) => a.localeCompare(b)));
	await writeFile(manifestPath, JSON.stringify(sorted, null, '\t') + '\n');
}

async function findSources(directories, extensions) {
	const patterns = directories.map((dir) => `${dir}/**/*.{${extensions.join(',')}}`);
	const files = await fastGlob(patterns, { cwd: repoRoot, absolute: true, caseSensitiveMatch: false });
	return files.sort();
}

function webpPathFor(sourcePath) {
	const ext = path.extname(sourcePath);
	return sourcePath.slice(0, -ext.length) + '.webp';
}

async function hashFile(filePath) {
	const buffer = await readFile(filePath);
	return createHash('sha256').update(buffer).digest('hex');
}

function isStale(relPath, sourceHash, webpFile, manifest) {
	if (force) return true;
	const entry = manifest[relPath];
	if (!entry || entry.sourceHash !== sourceHash) return true;
	if (entry.status === 'written' && !existsSync(webpFile)) return true;
	return false;
}

async function convertOne(sourcePath) {
	const webpFile = webpPathFor(sourcePath);
	const image = sharp(sourcePath);
	const metadata = await image.metadata();
	const options = metadata.hasAlpha ? PNG_QUALITY : LOSSY_QUALITY;
	const buffer = await image.webp(options).toBuffer();

	const sourceStat = await stat(sourcePath);
	if (buffer.length >= sourceStat.size) {
		return { webpFile, status: 'skipped-larger', bytesSaved: 0 };
	}

	mkdirSync(path.dirname(webpFile), { recursive: true });
	await writeFile(webpFile, buffer);
	return { webpFile, status: 'written', bytesSaved: sourceStat.size - buffer.length };
}

async function main() {
	const { directories, extensions } = loadTargets();
	const sources = await findSources(directories, extensions);

	if (sources.length === 0) {
		console.log('No source images found under configured target directories.');
		return;
	}

	const manifest = loadManifest();
	const stale = [];
	const sourceHashes = new Map();

	for (const sourcePath of sources) {
		const relPath = path.relative(repoRoot, sourcePath);
		const sourceHash = await hashFile(sourcePath);
		sourceHashes.set(sourcePath, sourceHash);
		const webpFile = webpPathFor(sourcePath);
		if (isStale(relPath, sourceHash, webpFile, manifest)) {
			stale.push(sourcePath);
		}
	}

	if (checkOnly) {
		if (stale.length === 0) {
			console.log(`OK: all ${sources.length} images have up-to-date .webp siblings.`);
			return;
		}
		console.error(`Missing or stale .webp for ${stale.length} file(s):`);
		for (const file of stale) {
			console.error(`  ${path.relative(repoRoot, file)}`);
		}
		console.error('\nRun `npm run media:webp` and commit the generated .webp / manifest changes.');
		process.exitCode = 1;
		return;
	}

	if (stale.length === 0) {
		console.log(`Nothing to do: all ${sources.length} images already have up-to-date .webp siblings.`);
		return;
	}

	let written = 0;
	let skippedLarger = 0;
	let totalSaved = 0;
	for (const sourcePath of stale) {
		const relPath = path.relative(repoRoot, sourcePath);
		const result = await convertOne(sourcePath);
		manifest[relPath] = { sourceHash: sourceHashes.get(sourcePath), status: result.status };

		if (result.status === 'written') {
			written += 1;
			totalSaved += result.bytesSaved;
			console.log(`webp  ${relPath} (+${(result.bytesSaved / 1024).toFixed(1)} KB saved)`);
		} else {
			skippedLarger += 1;
			console.log(`skip  ${relPath} (webp would be larger than source)`);
		}
	}

	await saveManifest(manifest);

	console.log(`\nConverted ${written} file(s), skipped ${skippedLarger} (webp larger than source).`);
	console.log(`Total saved: ${(totalSaved / 1024 / 1024).toFixed(2)} MB`);
}

main().catch((error) => {
	console.error(error);
	process.exitCode = 1;
});
