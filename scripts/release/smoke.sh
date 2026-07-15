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

"$curl_bin" --fail --silent --show-error --location --max-time 20 "$base_url/" >/dev/null
"$curl_bin" --fail --silent --show-error --location --max-time 20 "$base_url/wp-json/" >/dev/null
phone_headers="$("$curl_bin" --fail --silent --show-error --location --max-time 20 --head "$base_url/wp-json/logika/v1/phone-country")"
if ! grep -qi '^cache-control:.*no-store' <<<"$phone_headers"; then
  echo "Phone-country endpoint must return Cache-Control: no-store" >&2
  exit 1
fi

if ((expect_noindex)); then
  robots="$("$curl_bin" --fail --silent --show-error --location --max-time 20 "$base_url/robots.txt")"
  if ! grep -qi 'noindex' <<<"$robots"; then
    echo "Staging robots.txt must contain noindex" >&2
    exit 1
  fi
fi
