#!/bin/bash

if [ $(phpenv version-name) != "hhvm" ]; then
    pecl channel-update pecl.php.net

    pear config-set php_ini ''
    pecl config-set php_ini ''

    if [ $(phpenv version-name) = "7.0" ]; then
        echo "yes\nno\n" | pecl install apcu-5.1.2
        pecl install apcu_bc-beta
        phpenv config-add thinkphp/tests/conf/apcu_bc.ini

        wget -O phpredis-php7.tar.gz https://github.com/phpredis/phpredis/archive/php7.tar.gz
        tar -xzvf phpredis-php7.tar.gz
        cd phpredis-php7 && phpize && ./configure && make && sudo make install

        wget -O memcached-php7.tar.gz https://github.com/php-memcached-dev/php-memcached/archive/php7.zip
        tar -xzvf memcached-php7.tar.gz
        cd php-memcached-php7 && phpize && ./configure && make && sudo make install
    else
        echo "yes\nno\n" | pecl install apcu-4.0.10
        phpenv config-add thinkphp/tests/conf/apcu.ini
    fi
fi

phpenv config-add thinkphp/tests/conf/memcached.ini
phpenv config-add thinkphp/tests/conf/redis.ini

composer install --no-interaction --ignore-platform-reqs
