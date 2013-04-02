#!/bin/sh

current=$PWD

php bin/env.php
cd $current/apps/Helloworld/; phpunit --coverage-text
cd $current/apps/Sandbox/; phpunit  --coverage-text
cd $current; phpunit  --coverage-text
php-cs-fixer fix ./ --level=psr2  --dry-run
