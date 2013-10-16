#!/bin/sh
echo y | php app/console doctrine:mongodb:fixtures:load
phpunit -c app/phpunit.xml src/Bpi/ApiBundle/Tests/
