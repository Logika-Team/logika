#!/usr/bin/env bash

set -euo pipefail

usage() {
  cat <<'EOF'
Usage: deploy.sh --artifact PATH

Required environment:
  DEPLOY_HOST       SSH host
  DEPLOY_USER       SSH user with access to the release root
  DEPLOY_ROOT       Release root that contains releases/ and current
  DEPLOY_SITE_ROOT  Existing WordPress root; wp-config.php and uploads stay here

Optional environment:
  DEPLOY_PORT       SSH port (default: 22)
  WP_CLI_BIN        WP-CLI command on the remote host (default: wp)
EOF
}

artifact=""
while (($#)); do
  case "$1" in
    --artifact)
      artifact="$2"
      shift 2
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

: "${DEPLOY_HOST:?DEPLOY_HOST is required}"
: "${DEPLOY_USER:?DEPLOY_USER is required}"
: "${DEPLOY_ROOT:?DEPLOY_ROOT is required}"
: "${DEPLOY_SITE_ROOT:?DEPLOY_SITE_ROOT is required}"
deploy_port="${DEPLOY_PORT:-22}"
wp_cli_bin="${WP_CLI_BIN:-wp}"

if [[ -z "$artifact" || ! -f "$artifact" ]]; then
  echo "An existing --artifact is required" >&2
  exit 2
fi

archive_entries="$(tar -tzf "$artifact")"
if grep -Eq '(^|/)\.\.(/|$)' <<<"$archive_entries"; then
  echo "Release archive contains an unsafe path" >&2
  exit 1
fi

if grep -Ev '^(release-manifest\.json|wordpress/|wordpress/wp-content/|wordpress/wp-content/(themes|plugins)/|wordpress/wp-content/(themes/logika-theme|plugins/logika-core|plugins/logika-leads)/)' <<<"$archive_entries" | grep -q .; then
  echo "Release archive contains an unmanaged path" >&2
  exit 1
fi

release_id="$(tar -xOzf "$artifact" release-manifest.json | sed -n 's/.*"releaseId": "\([0-9a-f]\{40\}\)".*/\1/p')"
if [[ ! "$release_id" =~ ^[0-9a-f]{40}$ ]]; then
  echo "Release archive has no valid releaseId" >&2
  exit 1
fi

remote="$DEPLOY_USER@$DEPLOY_HOST"
remote_release_dir="$DEPLOY_ROOT/releases/$release_id"

ssh -p "$deploy_port" "$remote" "mkdir -p '$remote_release_dir'"
scp -P "$deploy_port" "$artifact" "$remote:$remote_release_dir/release.tar.gz"

ssh -p "$deploy_port" "$remote" \
  "DEPLOY_ROOT='$DEPLOY_ROOT' DEPLOY_SITE_ROOT='$DEPLOY_SITE_ROOT' RELEASE_ID='$release_id' WP_CLI_BIN='$wp_cli_bin' bash -s" <<'REMOTE_SCRIPT'
set -euo pipefail

wp_cli() {
  read -r -a wp_cli_parts <<<"$WP_CLI_BIN"
  "${wp_cli_parts[@]}" "$@"
}

release_dir="$DEPLOY_ROOT/releases/$RELEASE_ID"
archive="$release_dir/release.tar.gz"
expected_components=(
  "wp-content/themes/logika-theme"
  "wp-content/plugins/logika-core"
  "wp-content/plugins/logika-leads"
)

test -f "$archive"
mkdir -p "$release_dir/wordpress"
tar -xzf "$archive" -C "$release_dir"
test "$(sed -n 's/.*"releaseId": "\([0-9a-f]\{40\}\)".*/\1/p' "$release_dir/release-manifest.json")" = "$RELEASE_ID"

for component in "${expected_components[@]}"; do
  live_path="$DEPLOY_SITE_ROOT/$component"
  expected_target="$DEPLOY_ROOT/current/wordpress/$component"

  if [[ ! -L "$live_path" ]]; then
    echo "Bootstrap is required: $live_path must be a symlink to $expected_target" >&2
    exit 1
  fi

  if [[ "$(readlink "$live_path")" != "$expected_target" ]]; then
    echo "Unsafe managed component link: $live_path" >&2
    exit 1
  fi
done

ln -s "releases/$RELEASE_ID" "$DEPLOY_ROOT/current.next"
mv -Tf "$DEPLOY_ROOT/current.next" "$DEPLOY_ROOT/current"
wp_cli --path="$DEPLOY_SITE_ROOT" theme activate logika-theme
wp_cli --path="$DEPLOY_SITE_ROOT" plugin activate logika-core logika-leads
wp_cli --path="$DEPLOY_SITE_ROOT" theme is-active logika-theme
wp_cli --path="$DEPLOY_SITE_ROOT" plugin is-active logika-core
wp_cli --path="$DEPLOY_SITE_ROOT" plugin is-active logika-leads
wp_cli --path="$DEPLOY_SITE_ROOT" cache flush
REMOTE_SCRIPT
