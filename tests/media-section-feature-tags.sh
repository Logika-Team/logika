#!/usr/bin/env bash
set -euo pipefail

css="wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section-mobile.css"

test "$(grep -c 'feature-tags span.*height:45px;padding:10px 20px;font-size:18px;font-weight:600' "$css")" -eq 2
