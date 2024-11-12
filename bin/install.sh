#!/usr/bin/env bash

GF_LICENSE="${GF_LICENSE:=$1}"

# Add new variables / override existing if .env file exists
if [[ -f ".env" ]]; then
    set -a
    source .env
    set +a
fi

# Install Gravity PDF Dependencies
composer install
composer run prefix

# Start local environment
if [[ $PHP_ENABLE_XDEBUG ]]; then
  npm run wp-env start -- --upgrade --xdebug=debug,coverage
else
    npm run wp-env start -- --upgrade
fi

echo "Install Gravity Forms..."
bash ./bin/install-gravityforms.sh

npm run wp-env run cli wp plugin activate gravityforms gravityformscli gravity-pdf
npm run wp-env run tests-cli wp plugin activate gravityforms gravityformscli gravityformspolls gravityformssurvey gravityformsquiz gravity-pdf gravity-pdf-test-suite

# Place CLI config file
npm run wp-env run tests-wordpress cp /var/www/html/wp-content/plugins/gravity-pdf/bin/htaccess-sample /var/www/html/.htaccess

echo "Run Database changes"
bash ./bin/install-database.sh

if [[ -f "./bin/install-post-actions.sh" ]]; then
  echo "Running Post Install Actions..."
  bash ./bin/install-post-actions.sh
fi