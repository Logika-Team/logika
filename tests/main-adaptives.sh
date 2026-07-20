#!/usr/bin/env bash
set -euo pipefail

css="wordpress/wp-content/themes/logika-theme/assets/css/adaptive.css"

grep -q 'archive-section__box{display:grid' "$css"
grep -q 'article-section__content{display:grid' "$css"
grep -q 'offers-section__items{display:grid' "$css"
grep -q 'news-card{padding:clamp(20px,1.563vw,30px)' "$css"
grep -q 'tags{display:flex' "$css"
grep -q '@media(max-width:1024px){.article-section__content{grid-template-columns:100%}' "$css"
grep -q '@media(max-width:767px){.article-section__hero{flex-direction:column}' "$css"
