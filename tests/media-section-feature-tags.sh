#!/usr/bin/env bash
set -euo pipefail

css="wordpress/wp-content/themes/logika-theme/assets/css/blocks/sections/media-section-mobile.css"

grep -Fq 'feature-tags{top:20px;left:20px;width:calc(100% - 40px);height:auto;justify-content:center;flex-wrap:nowrap}' "$css"
grep -Fq 'feature-tags span,.media-center-section .media-section__feature-tags span:first-child,.media-center-section .media-section__feature-tags span:nth-child(2){flex:1 1 0;min-width:0;width:auto;max-width:none;height:45px;padding:8px;font-size:clamp(10px,3.2vw,18px);font-weight:600}' "$css"
