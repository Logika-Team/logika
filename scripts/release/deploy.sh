#!/usr/bin/env bash

set -euo pipefail

usage() {
  cat <<'EOF'
Usage: deploy.sh --artifact PATH [--bootstrap-wp-content]

Required environment:
  DEPLOY_HOST       SSH host
  DEPLOY_USER       SSH user with access to the release root
  DEPLOY_ROOT       Release root that contains releases/ and current
  DEPLOY_SITE_ROOT  Existing WordPress root; wp-config.php and uploads stay here

Optional environment:
  DEPLOY_PORT       SSH port (default: 22)
  WP_CLI_BIN        WP-CLI command on the remote host (default: wp)

--bootstrap-wp-content replaces an existing wp-content directory once. It requires
ALLOW_FULL_WP_CONTENT_BOOTSTRAP=1 and moves uploads to DEPLOY_ROOT/uploads.
EOF
}

artifact=""
bootstrap_wp_content=0
while (($#)); do
  case "$1" in
    --artifact)
      artifact="$2"
      shift 2
      ;;
    --bootstrap-wp-content)
      bootstrap_wp_content=1
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

if grep -Ev '^(release-manifest\.json|release-files\.sha256|wordpress/|wordpress/wp-content/)' <<<"$archive_entries" | grep -q .; then
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
  "DEPLOY_ROOT='$DEPLOY_ROOT' DEPLOY_SITE_ROOT='$DEPLOY_SITE_ROOT' RELEASE_ID='$release_id' WP_CLI_BIN='$wp_cli_bin' BOOTSTRAP_WP_CONTENT='$bootstrap_wp_content' ALLOW_FULL_WP_CONTENT_BOOTSTRAP='${ALLOW_FULL_WP_CONTENT_BOOTSTRAP:-}' bash -s" <<'REMOTE_SCRIPT'
set -euo pipefail

wp_cli() {
  read -r -a wp_cli_parts <<<"$WP_CLI_BIN"
  "${wp_cli_parts[@]}" "$@"
}

release_dir="$DEPLOY_ROOT/releases/$RELEASE_ID"
archive="$release_dir/release.tar.gz"
expected_components=(
  "wp-content"
)

test -f "$archive"
mkdir -p "$release_dir/wordpress"
tar -xzf "$archive" -C "$release_dir"
test "$(sed -n 's/.*"releaseId": "\([0-9a-f]\{40\}\)".*/\1/p' "$release_dir/release-manifest.json")" = "$RELEASE_ID"
( cd "$release_dir" && sha256sum -c release-files.sha256 )

persistent_uploads="$DEPLOY_ROOT/uploads"
if [[ "$BOOTSTRAP_WP_CONTENT" == "1" ]]; then
  [[ "$ALLOW_FULL_WP_CONTENT_BOOTSTRAP" == "1" ]] || { echo "Set ALLOW_FULL_WP_CONTENT_BOOTSTRAP=1 to bootstrap wp-content." >&2; exit 2; }
  live_path="$DEPLOY_SITE_ROOT/wp-content"
  [[ ! -L "$live_path" ]] || { echo "wp-content is already managed." >&2; exit 1; }
  [[ -d "$live_path/uploads" ]] || { echo "Existing uploads directory is required for bootstrap." >&2; exit 1; }
  [[ ! -e "$persistent_uploads" ]] || { echo "Persistent uploads already exist: $persistent_uploads" >&2; exit 1; }
fi

ln -s "releases/$RELEASE_ID" "$DEPLOY_ROOT/current.next"
mv -Tf "$DEPLOY_ROOT/current.next" "$DEPLOY_ROOT/current"

if [[ "$BOOTSTRAP_WP_CONTENT" == "1" ]]; then
  live_path="$DEPLOY_SITE_ROOT/wp-content"
  mkdir -p "$DEPLOY_ROOT/bootstrap-backups"
  mv "$live_path/uploads" "$persistent_uploads"
  mv "$live_path" "$DEPLOY_ROOT/bootstrap-backups/wp-content-$RELEASE_ID"
  ln -s "$DEPLOY_ROOT/current/wordpress/wp-content" "$live_path"
fi

ln -sfn "$persistent_uploads" "$release_dir/wordpress/wp-content/uploads"

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
wp_cli --path="$DEPLOY_SITE_ROOT" theme activate logika-theme
wp_cli --path="$DEPLOY_SITE_ROOT" plugin activate logika-core logika-leads
wp_cli --path="$DEPLOY_SITE_ROOT" theme is-active logika-theme
wp_cli --path="$DEPLOY_SITE_ROOT" plugin is-active logika-core
wp_cli --path="$DEPLOY_SITE_ROOT" plugin is-active logika-leads

ensure_page() {
	local slug="$1" title="$2" template="${3:-}" page_id
	page_id="$(wp_cli --path="$DEPLOY_SITE_ROOT" post list --post_type=page --post_status=any --name="$slug" --format=ids | awk '{ print $1 }')"
	if [[ -z "$page_id" ]]; then
		page_id="$(wp_cli --path="$DEPLOY_SITE_ROOT" post create --post_type=page --post_name="$slug" --post_title="$title" --post_status=publish --porcelain)"
	fi
	if [[ -n "$template" ]]; then
		wp_cli --path="$DEPLOY_SITE_ROOT" post meta update "$page_id" _wp_page_template "$template"
	fi
	echo "$page_id"
}

home_id="$(ensure_page home 'Головна')"
wp_cli --path="$DEPLOY_SITE_ROOT" option update show_on_front page
wp_cli --path="$DEPLOY_SITE_ROOT" option update page_on_front "$home_id"
ensure_page about 'Про Logika' 'templates/page-about.php' >/dev/null
ensure_page faq 'FAQ' 'templates/page-faq.php' >/dev/null
ensure_page it-courses 'Курси програмування' 'templates/page-it-courses.php' >/dev/null
ensure_page english-courses 'Курси англійської' 'templates/page-english-courses.php' >/dev/null
ensure_page media-center 'Медіацентр' 'templates/page-media-center.php' >/dev/null
ensure_page vacancies 'Вакансії' 'templates/page-vacancies.php' >/dev/null
ensure_page privacy-policy 'Політика конфіденційності' >/dev/null
ensure_page contractoffer 'Договір оферти' >/dev/null
ensure_page contractoffer-overseas 'Договір оферти для клієнтів поза межами України' >/dev/null
ensure_page litsenziia 'Освітня ліцензія' >/dev/null
wp_cli --path="$DEPLOY_SITE_ROOT" rewrite structure '/%postname%/'
wp_cli --path="$DEPLOY_SITE_ROOT" rewrite flush --hard
wp_cli --path="$DEPLOY_SITE_ROOT" cache flush
REMOTE_SCRIPT
