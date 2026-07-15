#!/usr/bin/env bash

set -euo pipefail

if [[ "${ALLOW_MANAGED_LINK_BOOTSTRAP:-}" != "1" ]]; then
  echo "Set ALLOW_MANAGED_LINK_BOOTSTRAP=1 only after a verified backup and rollback window." >&2
  exit 2
fi

: "${DEPLOY_HOST:?DEPLOY_HOST is required}"
: "${DEPLOY_USER:?DEPLOY_USER is required}"
: "${DEPLOY_ROOT:?DEPLOY_ROOT is required}"
: "${DEPLOY_SITE_ROOT:?DEPLOY_SITE_ROOT is required}"

deploy_port="${DEPLOY_PORT:-22}"
remote="$DEPLOY_USER@$DEPLOY_HOST"

ssh -p "$deploy_port" "$remote" \
  "DEPLOY_ROOT='$DEPLOY_ROOT' DEPLOY_SITE_ROOT='$DEPLOY_SITE_ROOT' bash -s" <<'REMOTE_SCRIPT'
set -euo pipefail

for component in wp-content/themes/logika-theme wp-content/plugins/logika-core wp-content/plugins/logika-leads; do
  live_path="$DEPLOY_SITE_ROOT/$component"
  target="$DEPLOY_ROOT/current/wordpress/$component"

  if [[ -e "$live_path" && ! -L "$live_path" ]]; then
    echo "Refusing to replace existing non-symlink component: $live_path" >&2
    exit 1
  fi

  mkdir -p "$(dirname "$live_path")"
  ln -sfn "$target" "$live_path"
done
REMOTE_SCRIPT
