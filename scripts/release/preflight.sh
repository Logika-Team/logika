#!/usr/bin/env bash

set -euo pipefail

: "${DEPLOY_HOST:?DEPLOY_HOST is required}"
: "${DEPLOY_USER:?DEPLOY_USER is required}"
: "${DEPLOY_SITE_ROOT:?DEPLOY_SITE_ROOT is required}"

deploy_port="${DEPLOY_PORT:-22}"
wp_cli_bin="${WP_CLI_BIN:-wp}"
remote="$DEPLOY_USER@$DEPLOY_HOST"

ssh -p "$deploy_port" "$remote" \
  "DEPLOY_SITE_ROOT='$DEPLOY_SITE_ROOT' WP_CLI_BIN='$wp_cli_bin' bash -s" <<'REMOTE_SCRIPT'
set -euo pipefail

wp_cli() {
  read -r -a wp_cli_parts <<<"$WP_CLI_BIN"
  "${wp_cli_parts[@]}" "$@"
}

wp_cli --path="$DEPLOY_SITE_ROOT" core version
wp_cli --path="$DEPLOY_SITE_ROOT" theme is-active logika-theme
wp_cli --path="$DEPLOY_SITE_ROOT" plugin is-active logika-core
wp_cli --path="$DEPLOY_SITE_ROOT" plugin is-active logika-leads
wp_cli --path="$DEPLOY_SITE_ROOT" eval 'echo wp_json_encode( array_keys( rest_get_server()->get_routes() ) );'
wp_cli --path="$DEPLOY_SITE_ROOT" eval '$request = new WP_REST_Request( "GET", "/logika/v1/phone-country" ); $response = rest_do_request( $request ); if ( 200 !== $response->get_status() ) { fwrite( STDERR, "Phone-country REST endpoint returned " . $response->get_status() . PHP_EOL ); exit( 1 ); } $headers = $response->get_headers(); $cache = $headers["Cache-Control"] ?? $headers["cache-control"] ?? ""; if ( false === stripos( (string) $cache, "no-store" ) ) { fwrite( STDERR, "Phone-country endpoint must return Cache-Control: no-store" . PHP_EOL ); exit( 1 ); } echo wp_json_encode( $response->get_data() );'
REMOTE_SCRIPT
