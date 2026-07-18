#!/usr/bin/env bash
set -euo pipefail

css="wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section-mobile.css"
markup="wordpress/wp-content/themes/logika-theme/source-pages/index.php"

grep -q '<section class="media-section media-center-section">' "$markup"
grep -q '@media(max-width:767px)' "$css"
grep -q 'grid-template-columns:1fr' "$css"
grep -q 'height:auto' "$css"
grep -q 'padding:20px' "$css"
grep -q 'calc(100% - 40px)' "$css"
grep -q 'aspect-ratio:710/410' "$css"
grep -q 'object-fit:contain' "$css"
grep -q 'max-width:186px' "$css"
grep -q 'max-width:192px' "$css"
grep -q 'font-weight:700' "$css"
grep -q 'line-height:1.18' "$css"
grep -q 'line-height:1.4' "$css"
grep -q '@media(min-width:768px) and (max-width:1199px)' "$css"
grep -q 'repeat(2,minmax(0,1fr))' "$css"
grep -q 'width:calc(100% - 40px)' "$css"
if grep -q 'top:358px' "$css"; then
  exit 1
fi
