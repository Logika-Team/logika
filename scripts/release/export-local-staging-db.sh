#!/usr/bin/env bash

set -euo pipefail

usage() {
  cat <<'EOF'
Usage: export-local-staging-db.sh [--output-dir DIR] [--no-upload]

Exports the local WordPress database for staging only. The local database is not
modified: URL replacement is performed into an exported SQL file.

Optional environment:
  LOCAL_URL         Local WordPress home URL. Auto-detected with WP-CLI when omitted.
  STAGING_URL       Target staging URL (default: https://staging.logika.resumemyhost.miy.link)
  LOCAL_WP_CLI_BIN  Local WP-CLI command. Defaults to `ddev wp` when .ddev exists, then `wp`.
                    With DDEV, --output-dir must be project-relative.

Upload environment, all optional unless upload is desired:
  DEPLOY_HOST       SSH host
  DEPLOY_USER       SSH user
  DEPLOY_ROOT       Release root; dump is uploaded to DEPLOY_ROOT/incoming/staging-db.sql.gz
  DEPLOY_PORT       SSH port (default: 22)
  SSH_KEY           Optional private key path for ssh/scp -i

This script never uploads to production and never writes a SQL dump into Git.
EOF
}

output_dir="${OUTPUT_DIR:-release-artifacts/staging-db}"
upload=1

while (($#)); do
  case "$1" in
    --output-dir)
      output_dir="$2"
      shift 2
      ;;
    --no-upload)
      upload=0
      shift
      ;;
    --help|-h)
      usage
      exit 0
      ;;
    *)
      echo "Unknown argument: $1" >&2
      usage >&2
      exit 2
      ;;
  esac
done

staging_url="${STAGING_URL:-https://staging.logika.resumemyhost.miy.link}"
production_url="${PRODUCTION_URL:-https://logika.resumemyhost.miy.link}"
timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
sql_path="$output_dir/staging-db-$timestamp.sql"
gzip_path="$sql_path.gz"
use_ddev=0

if [[ -z "${LOCAL_WP_CLI_BIN:-}" && -f .ddev/config.yaml ]] && command -v ddev >/dev/null 2>&1; then
  use_ddev=1
fi

if [[ "$use_ddev" -eq 1 && "$output_dir" = /* ]]; then
  echo "When using DDEV, --output-dir must be project-relative so the container can write the export." >&2
  exit 2
fi

wp_cli() {
  if [[ -n "${LOCAL_WP_CLI_BIN:-}" ]]; then
    read -r -a wp_cli_parts <<<"$LOCAL_WP_CLI_BIN"
    "${wp_cli_parts[@]}" "$@"
    return
  fi

  if [[ "$use_ddev" -eq 1 ]]; then
    ddev wp "$@"
    return
  fi

  wp "$@"
}

mkdir -p "$output_dir"

local_url="${LOCAL_URL:-}"
if [[ -z "$local_url" ]]; then
  local_url="$(wp_cli option get home)"
fi

if [[ -z "$local_url" ]]; then
  echo "Unable to detect LOCAL_URL" >&2
  exit 1
fi

if [[ "$local_url" == "$staging_url" || "$local_url" == "$production_url" ]]; then
  echo "Refusing to export from a staging or production URL: $local_url" >&2
  exit 1
fi

wp_cli search-replace "$local_url" "$staging_url" \
  --all-tables \
  --precise \
  --recurse-objects \
  --skip-columns=guid \
  --export="$sql_path"

gzip -9f "$sql_path"
gzip -t "$gzip_path"

if gzip -cd "$gzip_path" | grep -Fq "$local_url"; then
  echo "Exported dump still contains the local URL: $local_url" >&2
  exit 1
fi

if gzip -cd "$gzip_path" | grep -Fq '.ddev.site'; then
  echo "Exported dump still contains a DDEV URL" >&2
  exit 1
fi

if gzip -cd "$gzip_path" | grep -Fq 'localhost'; then
  echo "Exported dump still contains localhost references" >&2
  exit 1
fi

if gzip -cd "$gzip_path" | grep -Fq "$production_url"; then
  echo "Exported dump contains the production URL: $production_url" >&2
  exit 1
fi

printf 'Created staging database dump: %s\n' "$gzip_path"

if [[ "$upload" -eq 0 ]]; then
  exit 0
fi

if [[ -z "${DEPLOY_HOST:-}" || -z "${DEPLOY_USER:-}" || -z "${DEPLOY_ROOT:-}" ]]; then
  echo "Upload skipped: DEPLOY_HOST, DEPLOY_USER and DEPLOY_ROOT are not all set."
  exit 0
fi

deploy_port="${DEPLOY_PORT:-22}"
remote="$DEPLOY_USER@$DEPLOY_HOST"
remote_tmp="$DEPLOY_ROOT/incoming/staging-db.sql.gz.tmp"
remote_final="$DEPLOY_ROOT/incoming/staging-db.sql.gz"
ssh_args=(-p "$deploy_port")
scp_args=(-P "$deploy_port")

if [[ -n "${SSH_KEY:-}" ]]; then
  ssh_args+=(-i "$SSH_KEY")
  scp_args+=(-i "$SSH_KEY")
fi

ssh "${ssh_args[@]}" "$remote" "umask 077 && mkdir -p '$DEPLOY_ROOT/incoming'"
scp "${scp_args[@]}" "$gzip_path" "$remote:$remote_tmp"
ssh "${ssh_args[@]}" "$remote" "umask 077 && gzip -t '$remote_tmp' && mv -f '$remote_tmp' '$remote_final'"

printf 'Uploaded pending staging database dump to %s:%s\n' "$DEPLOY_HOST" "$remote_final"
