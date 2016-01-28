#!/bin/bash

if [ $(phpenv version-name) != "hhvm" ]; then
    cp thinkphp/tests/extensions/$(phpenv version-name)/*.so $(php-config --extension-dir)

    if [ $(phpenv version-name) = "7.0" ]; then
        phpenv config-add thinkphp/tests/conf/apcu_bc.ini
    else
        phpenv config-add thinkphp/tests/conf/apcu.ini
    fi

    phpenv config-add thinkphp/tests/conf/memcached.ini
    phpenv config-add thinkphp/tests/conf/redis.ini
fi

composer install --no-interaction --ignore-platform-reqs
