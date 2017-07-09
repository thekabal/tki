#!/usr/bin/env bash
/usr/bin/php /var/www/html/tki/vendor/bin/phpmd . text /var/www/html/tki/vendor/bin/phpmd.xml --exclude vendor/,templates
/usr/bin/php /var/www/html/tki/vendor/bin/phpcs -q --standard=/var/www/html/tki/vendor/bin/phpcs.xml . --ignore=templates,vendor
/usr/bin/php /var/www/html/tki/vendor/bin/phpstan analyze --no-progress --no-ansi -l 5 -c /var/www/html/tki/vendor/bin/phpstan.neon -vvv .

