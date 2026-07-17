#!/usr/bin/env bash

set -euo pipefail

: "${DEPLOY_HOST:?DEPLOY_HOST is required}"
: "${DEPLOY_USER:?DEPLOY_USER is required}"
: "${DEPLOY_ROOT:?DEPLOY_ROOT is required}"
: "${DEPLOY_SITE_ROOT:?DEPLOY_SITE_ROOT is required}"

deploy_port="${DEPLOY_PORT:-22}"
wp_cli_bin="${WP_CLI_BIN:-wp}"
staging_url="${STAGING_URL:-https://staging.logika.resumemyhost.miy.link}"
remote="$DEPLOY_USER@$DEPLOY_HOST"

ssh -p "$deploy_port" "$remote" \
  "DEPLOY_ROOT='$DEPLOY_ROOT' DEPLOY_SITE_ROOT='$DEPLOY_SITE_ROOT' WP_CLI_BIN='$wp_cli_bin' STAGING_URL='$staging_url' bash -s" <<'REMOTE_SCRIPT'
set -euo pipefail
umask 077

wp_cli() {
  read -r -a wp_cli_parts <<<"$WP_CLI_BIN"
  "${wp_cli_parts[@]}" "$@"
}

pending_dump="$DEPLOY_ROOT/incoming/staging-db.sql.gz"
if [[ ! -f "$pending_dump" ]]; then
  echo "No pending staging database dump"
  exit 0
fi

backup_id="$(date -u +%Y%m%dT%H%M%SZ)"
backup_root="$DEPLOY_ROOT/db-backups"
backup_dir="$backup_root/$backup_id"
tmp_sql="$backup_dir/staging-db.sql"
imported_dump="$DEPLOY_ROOT/incoming/staging-db.imported-$backup_id.sql.gz"

mkdir -p "$backup_dir"
gzip -t "$pending_dump"

wp_cli --path="$DEPLOY_SITE_ROOT" db export "$backup_dir/database.sql"
gzip -9 "$backup_dir/database.sql"
sha256sum "$pending_dump" "$backup_dir/database.sql.gz" > "$backup_dir/SHA256SUMS"

gzip -dc "$pending_dump" > "$tmp_sql"
source_prefix="$(sed -n 's/^CREATE TABLE `\([^`]*\)options`.*/\1/p' "$tmp_sql" | head -n 1)"
target_prefix="$(wp_cli --path="$DEPLOY_SITE_ROOT" config get table_prefix)"

if [[ ! "$source_prefix" =~ ^[A-Za-z0-9_]+$ || ! "$target_prefix" =~ ^[A-Za-z0-9_]+$ ]]; then
  echo "Could not determine safe WordPress table prefixes" >&2
  exit 1
fi

if [[ "$source_prefix" != "$target_prefix" ]]; then
  SOURCE_PREFIX="$source_prefix" TARGET_PREFIX="$target_prefix" \
    perl -pi -e 's/`\Q$ENV{SOURCE_PREFIX}\E/`$ENV{TARGET_PREFIX}/g' "$tmp_sql"
fi

wp_cli --path="$DEPLOY_SITE_ROOT" db import "$tmp_sql"
rm -f "$tmp_sql"

if [[ "$source_prefix" != "$target_prefix" ]]; then
  wp_cli --path="$DEPLOY_SITE_ROOT" db query "UPDATE \`${target_prefix}usermeta\` SET meta_key = REPLACE(meta_key, '${source_prefix}', '${target_prefix}') WHERE meta_key LIKE '${source_prefix}%';"
  wp_cli --path="$DEPLOY_SITE_ROOT" db query "UPDATE \`${target_prefix}options\` SET option_name = REPLACE(option_name, '${source_prefix}', '${target_prefix}') WHERE option_name = '${source_prefix}user_roles';"
fi

wp_cli --path="$DEPLOY_SITE_ROOT" option update home "$STAGING_URL"
wp_cli --path="$DEPLOY_SITE_ROOT" option update siteurl "$STAGING_URL"
wp_cli --path="$DEPLOY_SITE_ROOT" option update blog_public 0
wp_cli --path="$DEPLOY_SITE_ROOT" cache flush

mv -f "$pending_dump" "$imported_dump"
find "$backup_root" -mindepth 1 -maxdepth 1 -type d -mtime +14 -exec rm -rf {} +
find "$DEPLOY_ROOT/incoming" -maxdepth 1 -type f -name 'staging-db.imported-*.sql.gz' -mtime +14 -delete

printf 'Imported staging database dump. Previous database backup: %s\n' "$backup_dir"
REMOTE_SCRIPT
