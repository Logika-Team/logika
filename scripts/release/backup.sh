#!/usr/bin/env bash

set -euo pipefail

: "${DEPLOY_HOST:?DEPLOY_HOST is required}"
: "${DEPLOY_USER:?DEPLOY_USER is required}"
: "${DEPLOY_ROOT:?DEPLOY_ROOT is required}"
: "${DEPLOY_SITE_ROOT:?DEPLOY_SITE_ROOT is required}"

deploy_port="${DEPLOY_PORT:-22}"
wp_cli_bin="${WP_CLI_BIN:-wp}"
backup_root="${DEPLOY_BACKUP_ROOT:-$DEPLOY_ROOT/backups}"
remote="$DEPLOY_USER@$DEPLOY_HOST"

ssh -p "$deploy_port" "$remote" \
  "DEPLOY_BACKUP_ROOT='$backup_root' DEPLOY_SITE_ROOT='$DEPLOY_SITE_ROOT' WP_CLI_BIN='$wp_cli_bin' bash -s" <<'REMOTE_SCRIPT'
set -euo pipefail
umask 077

wp_cli() {
  read -r -a wp_cli_parts <<<"$WP_CLI_BIN"
  "${wp_cli_parts[@]}" "$@"
}

backup_id="$(date -u +%Y%m%dT%H%M%SZ)"
backup_dir="$DEPLOY_BACKUP_ROOT/$backup_id"
mkdir -p "$backup_dir"
wp_cli --path="$DEPLOY_SITE_ROOT" db export "$backup_dir/database.sql"
gzip -9 "$backup_dir/database.sql"
tar -C "$DEPLOY_SITE_ROOT" -czf "$backup_dir/managed-files.tar.gz" wp-config.php wp-content/uploads
sha256sum "$backup_dir/database.sql.gz" "$backup_dir/managed-files.tar.gz" > "$backup_dir/SHA256SUMS"
find "$DEPLOY_BACKUP_ROOT" -mindepth 1 -maxdepth 1 -type d -mtime +30 -exec rm -rf {} +
printf '%s\n' "$backup_dir"
REMOTE_SCRIPT
