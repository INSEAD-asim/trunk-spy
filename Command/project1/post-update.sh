#!/bin/sh
cd /path/to/project1
svn update --config-dir="%2" --username="%3" --password="%4"
php /path/to/project1/app/phpunit.phar^
 -c /path/to/project1/app/phpunit.xml^
 --log-tap %1^
 >/path/to/unit-test-detail.txt