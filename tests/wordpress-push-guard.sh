#!/bin/sh
set -eu

hook="$(dirname "$0")/../.githooks/pre-push"

printf '%s\n' 'refs/heads/wordpress deadbeef refs/heads/wordpress deadbeef' | "$hook"
if printf '%s\n' 'refs/heads/wordpress deadbeef refs/heads/main deadbeef' | "$hook"; then
    echo 'expected push to main to be rejected' >&2
    exit 1
fi
if printf '%s\n' 'refs/heads/main deadbeef refs/heads/wordpress deadbeef' | "$hook"; then
    echo 'expected main to wordpress push to be rejected' >&2
    exit 1
fi
