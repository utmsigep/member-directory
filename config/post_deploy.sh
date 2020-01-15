##!/usr/bin/env bash

composer install

npm ci

npm run-script build

php bin/console doctrine:migrations:migrate

php bin/console cache:warmup
