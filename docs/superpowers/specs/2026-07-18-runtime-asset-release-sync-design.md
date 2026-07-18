# Runtime Asset Release Sync Design

## Goal

Every WordPress release must contain current CSS, JavaScript and image assets
derived from the checked-in frontend source, without copying source files,
tests, uploads, `wp-config.php` or database data to the server.

## Design

`scripts/release/build-artifact.sh` remains the only artifact builder. Before
it archives a release, it runs the existing `npm run backend` build. It stages
the three managed WordPress components in a temporary directory, then overlays
`build/css`, `build/js` and `build/img` onto
`wordpress/wp-content/themes/logika-theme/assets` in that temporary theme.

The archive still contains only `logika-theme`, `logika-core` and
`logika-leads`. Runtime files that exist only in the theme are preserved; a
generated file with the same path replaces its previous theme copy. The local
theme and working tree are not modified by this overlay.

## Safety and verification

- Artifact checksums are calculated from the staged components after the
  overlay, so the manifest identifies the exact deployed runtime files.
- `tests/release-infrastructure.test.mjs` must prove generated CSS, JS and
  image files are present in the archive and that unmanaged paths are absent.
- Deployment continues to use the existing atomic `current` symlink switch,
  backups and server preflight.
- The artifact build fails if the frontend build fails; an incomplete asset
  release cannot be deployed.
