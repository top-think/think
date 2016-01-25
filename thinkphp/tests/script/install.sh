#!/bin/bash

if [ $(phpenv version-name) != "hhvm" ]; then
    pecl channel-update pecl.php.net

    pear config-set php_ini ''
    pecl config-set php_ini ''

    if [ $(phpenv version-name) = "7.0" ]; then
        echo "yes\nno\n" | pecl install apcu-5.1.2
        pecl install apcu_bc-beta
        phpenv config-add thinkphp/tests/conf/apcu_bc.ini
    else
        echo "yes\nno\n" | pecl install apcu-4.0.10
        phpenv config-add thinkphp/tests/conf/apcu.ini
    fi
fi

composer install --no-interaction --ignore-platform-reqs
