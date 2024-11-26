#!/usr/bin/env bash

exists() {
  command -v "$1" >/dev/null 2>&1
}

if [ -z "$PLUGIN_DIR" ]; then
  PLUGIN_DIR="./"
fi

PHP="php"
COMPOSER="composer"

rm -R "${PLUGIN_DIR}vendor_prefixed"
mkdir "${PLUGIN_DIR}vendor_prefixed"
touch "${PLUGIN_DIR}vendor_prefixed/.gitkeep"

# Monolog
eval "$PHP ${PLUGIN_DIR}vendor/bin/php-scoper add-prefix --output-dir=${PLUGIN_DIR}vendor_prefixed/monolog --config=${PLUGIN_DIR}.php-scoper/monolog.php -n -vvv"
eval "rm -Rf ${PLUGIN_DIR}vendor/monolog"

# URL Signer
eval "$PHP ${PLUGIN_DIR}vendor/bin/php-scoper add-prefix --output-dir=${PLUGIN_DIR}vendor_prefixed --config=${PLUGIN_DIR}.php-scoper/url-signer.php -n -vvv"
eval "rm -Rf ${PLUGIN_DIR}vendor/spatie"
eval "rm -Rf ${PLUGIN_DIR}vendor/league"

# Querypath
eval "$PHP ${PLUGIN_DIR}vendor/bin/php-scoper add-prefix --output-dir=${PLUGIN_DIR}vendor_prefixed --config=${PLUGIN_DIR}.php-scoper/querypath.php -n -vvv"
eval "rm -Rf ${PLUGIN_DIR}vendor/masterminds"

# Codeguy
eval "$PHP ${PLUGIN_DIR}vendor/bin/php-scoper add-prefix --output-dir=${PLUGIN_DIR}vendor_prefixed/gravitypdf/upload --config=${PLUGIN_DIR}.php-scoper/upload.php -n -vvv"

# Mpdf
eval "$PHP ${PLUGIN_DIR}vendor/bin/php-scoper add-prefix --output-dir=${PLUGIN_DIR}vendor_prefixed --config=${PLUGIN_DIR}.php-scoper/mpdf.php -n -vvv"
eval "rm -Rf ${PLUGIN_DIR}vendor/mpdf"
eval "rm -Rf ${PLUGIN_DIR}vendor/setasign"
eval "rm -Rf ${PLUGIN_DIR}vendor/myclabs"

# Do this at the end as we have multiple vendor packages used
eval "rm -Rf ${PLUGIN_DIR}vendor/gravitypdf"

eval "$COMPOSER dump-autoload --optimize --working-dir ${PLUGIN_DIR}"
