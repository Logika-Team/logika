#!/usr/bin/env bash
set -euo pipefail

if ! command -v ddev >/dev/null 2>&1; then
  echo "ddev is required to run the Logika WordPress MCP server." >&2
  exit 127
fi

if [ ! -d ".ddev" ]; then
  echo "DDEV is not configured in this checkout yet; run this from the WordPress project root after DDEV setup." >&2
  exit 1
fi

exec ddev wp "$@"
