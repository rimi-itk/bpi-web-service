#!/usr/bin/env bash
service mongodb start
service nginx start
php-fpm5.6 -F --pid /var/run/php/php-fpm5.6.pid -y /etc/php/5.6/fpm/php-fpm.conf
