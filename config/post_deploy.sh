##!/usr/bin/env bash

composer install

npm ci

npm run-script build
