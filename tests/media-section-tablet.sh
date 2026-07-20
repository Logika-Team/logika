#!/usr/bin/env bash
set -euo pipefail

sed -n '1,/^}/p' wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section-mobile.css | grep -q 'grid-template-columns:1fr'
grep -q 'media-section__contest p{max-width:100%;font-size:16px;font-weight:600;line-height:140%;letter-spacing:-.05em' wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section-mobile.css
