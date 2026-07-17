#!/usr/bin/env bash

set -euo pipefail

if ! command -v ddev >/dev/null 2>&1; then
  echo "DDEV is required to run the WordPress integration tests." >&2
  exit 127
fi

ddev start --skip-hooks
ddev exec scripts/release/prepare-wordpress-tests.sh

tests=(
  tests/wordpress-smoke.php
  tests/theme-source-pages.php
  tests/homepage-data.php
  tests/homepage-image-overrides.php
  tests/homepage-image-overrides-runtime.php
  tests/homepage-image-overrides-static.php
  tests/homepage-section-images.php
  tests/city-page.php
  tests/course-page.php
  tests/camp-page.php
  tests/city-api.php
  tests/city-faq-schema.php
  tests/city-schema.php
  tests/city-seo.php

  tests/legacy-city-redirect.php
  tests/course-schema.php
  tests/context-form.php
  tests/editor-experience.php
  tests/leads-reliability.php
  tests/leads.php
  tests/phone-country.php
)

for test_file in "${tests[@]}"; do
  ddev exec php "/var/www/html/$test_file"

  if [[ "$test_file" == "tests/city-page.php" ]]; then
    ddev exec wp eval-file --path=wordpress scripts/seed-cities.php
    ddev exec wp eval-file --path=wordpress scripts/sync-tilda-school-map.php
  fi
done
