#!/bin/bash

if wp core is-installed --url="${DDEV_PRIMARY_URL}"; then
    echo "WordPress @ ${DDEV_PRIMARY_URL} is already installed"
    exit 0
fi

echo "Installing ${DDEV_PRIMARY_URL}"

wp core install \
    --title="${DDEV_PROJECT}" \
    --admin_user="admin" \
    --admin_password="password" \
    --url="${DDEV_PRIMARY_URL}" \
    --admin_email="wp@${DDEV_HOSTNAME}" \
    --skip-email
wp plugin uninstall akismet
wp plugin uninstall hello

# wp plugin activate "${DDEV_PROJECT}"
wp plugin install plugin-check --activate
wp option update timezone_string "Europe/Vienna"
wp plugin install query-monitor --activate
wp plugin install user-switching --activate
wp language core update
wp language plugin install --all de_DE
wp language theme install --all de_DE
wp option update permalink_structure "/%postname%/" --quiet
wp rewrite flush --hard  --quiet
## End of opinionated
wp language plugin update --all
wp language theme update --all
