#!/usr/bin/env bash

set -euo pipefail

usage() {
  cat <<'EOF'
Usage: build-artifact.sh [--source-root PATH] [--output-dir PATH]

Builds a release archive containing the complete WordPress runtime content tree.
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

"$source_root/scripts/release/release-source-status.sh" "$source_root" >&2

components=("wordpress/wp-content")

for component in "${components[@]}"; do
  if [[ ! -d "$source_root/$component" ]]; then
    echo "Required release component is missing: $component" >&2
    exit 1
  fi
done

commit_sha="$(git -C "$source_root" rev-parse HEAD)"
commit_timestamp="$(git -C "$source_root" show -s --format=%cI HEAD)"
staging_dir="$(mktemp -d)"

cleanup() {
  rm -rf "$staging_dir"
}
trap cleanup EXIT

tar -C "$source_root" \
  --exclude='wordpress/wp-content/uploads' \
  --exclude='wordpress/wp-content/upgrade*' \
  -cf - "${components[@]}" | tar -C "$staging_dir" -xf -

(
  cd "$staging_dir"
  find wordpress -type f -print0 | sort -z | xargs -0 sha256sum
) > "$staging_dir/release-files.sha256"

release_id="$(sha256sum "$staging_dir/release-files.sha256" | awk '{print substr($1, 1, 40)}')"
archive_path="$output_dir/logika-wordpress-$release_id.tar.gz"
manifest_path="$output_dir/release-manifest.json"

component_checksums=()
for component in "${components[@]}"; do
  checksum="$(tar -C "$staging_dir" -cf - "$component" | sha256sum | awk '{print $1}')"
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
    "${components[0]}"
  ],
  "componentChecksums": {
    "${components[0]}": "${component_checksums[0]}"
  }
}
EOF

tar -C "$staging_dir" -czf "$archive_path" release-manifest.json release-files.sha256 wordpress
cp "$staging_dir/release-manifest.json" "$manifest_path"
sha256sum "$archive_path" > "$archive_path.sha256"

printf '%s\n' "$archive_path"
