#!/usr/bin/env bash

set -euo pipefail

cd /var/www/html

if [[ ! -f wordpress/wp-load.php ]]; then
  wp core download --path=wordpress --skip-content --force
fi

if ! wp core is-installed --path=wordpress >/dev/null 2>&1; then
  wp config create --path=wordpress --dbname=db --dbuser=db --dbpass=db --dbhost=db --skip-check --force
  wp core install --path=wordpress --url=http://logika.ddev.site --title=Logika-CI --admin_user=logika_ci --admin_password=local-ci-only --admin_email=ci@example.invalid --skip-email
fi

rm -rf wordpress/wp-content/plugins/advanced-custom-fields-pro
cp -a plugins/advanced-custom-fields-pro wordpress/wp-content/plugins/advanced-custom-fields-pro
wp plugin activate --path=wordpress advanced-custom-fields-pro logika-core logika-leads
wp theme activate --path=wordpress logika-theme

home_id="$(wp post list --path=wordpress --post_type=page --name=home --format=ids)"
if [[ -z "$home_id" ]]; then
  home_id="$(wp post create --path=wordpress --post_type=page --post_name=home --post_title=Home --post_status=publish --porcelain)"
fi
wp option update --path=wordpress show_on_front page
wp option update --path=wordpress page_on_front "$home_id"
wp cache flush --path=wordpress
