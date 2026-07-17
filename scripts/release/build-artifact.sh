#!/usr/bin/env bash

set -euo pipefail

usage() {
  cat <<'EOF'
Usage: build-artifact.sh [--source-root PATH] [--output-dir PATH]

Builds a release archive containing only the Logika WordPress theme and plugins.
Prints the archive path on stdout.
EOF
}

source_root="$PWD"
output_dir="$PWD/release-artifacts"

while (($#)); do
  case "$1" in
    --source-root)
      source_root="$2"
      shift 2
      ;;
    --output-dir)
      output_dir="$2"
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

source_root="$(cd "$source_root" && pwd -P)"
mkdir -p "$output_dir"
output_dir="$(cd "$output_dir" && pwd -P)"

components=(
  "wordpress/wp-content/themes/logika-theme"
  "wordpress/wp-content/plugins/logika-core"
  "wordpress/wp-content/plugins/logika-leads"
)

for component in "${components[@]}"; do
  if [[ ! -d "$source_root/$component" ]]; then
    echo "Required release component is missing: $component" >&2
    exit 1
  fi
done

commit_sha="$(git -C "$source_root" rev-parse HEAD)"
commit_timestamp="$(git -C "$source_root" show -s --format=%cI HEAD)"
release_id="$commit_sha"
archive_path="$output_dir/logika-wordpress-$release_id.tar.gz"
manifest_path="$output_dir/release-manifest.json"
staging_dir="$(mktemp -d)"

cleanup() {
  rm -rf "$staging_dir"
}
trap cleanup EXIT

component_checksums=()
for component in "${components[@]}"; do
  checksum="$(tar -C "$source_root" -cf - "$component" | sha256sum | awk '{print $1}')"
  component_checksums+=("$checksum")
done

cat > "$staging_dir/release-manifest.json" <<EOF
{
  "formatVersion": 1,
  "releaseId": "$release_id",
  "commitSha": "$commit_sha",
  "commitTimestamp": "$commit_timestamp",
  "migrations": { "required": false },
  "components": [
    "${components[0]}",
    "${components[1]}",
    "${components[2]}"
  ],
  "componentChecksums": {
    "${components[0]}": "${component_checksums[0]}",
    "${components[1]}": "${component_checksums[1]}",
    "${components[2]}": "${component_checksums[2]}"
  }
}
EOF

tar -C "$source_root" -cf - "${components[@]}" | tar -C "$staging_dir" -xf -
tar -C "$staging_dir" -czf "$archive_path" release-manifest.json wordpress
cp "$staging_dir/release-manifest.json" "$manifest_path"
sha256sum "$archive_path" > "$archive_path.sha256"

printf '%s\n' "$archive_path"
