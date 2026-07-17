#!/usr/bin/env bash

set -euo pipefail

usage() {
  cat <<'EOF'
Usage: smoke.sh --base-url URL [--expect-noindex]

Runs read-only HTTP checks. It never submits a lead or calls CRM.
EOF
}

base_url=""
expect_noindex=0
while (($#)); do
  case "$1" in
    --base-url)
      base_url="$2"
      shift 2
      ;;
    --expect-noindex)
      expect_noindex=1
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

if [[ -z "$base_url" ]]; then
  echo "--base-url is required" >&2
  exit 2
fi

curl_bin="${CURL_BIN:-curl}"
base_url="${base_url%/}"
curl_args=(--fail --silent --show-error --location --max-time 20 --retry 2 --retry-delay 10 --retry-all-errors)

homepage="$("$curl_bin" "${curl_args[@]}" "$base_url/")"
if ! grep -q 'data-logika-lead-form' <<<"$homepage"; then
  echo "Homepage must render the Logika lead form" >&2
  exit 1
fi

if ((expect_noindex)); then
  if ! grep -qi 'noindex' <<<"$homepage"; then
    echo "Staging homepage must contain noindex" >&2
    exit 1
  fi
fi
