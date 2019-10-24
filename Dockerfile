FROM itkdev/php5.6-fpm

COPY . /app
WORKDIR /app

RUN apt-get update --yes \
	&& DEBIAN_FRONTEND=noninteractive \
	apt-get install --yes \
	php-pear \
	php5.6-dev \
	libssl-dev \
	mongodb \
	nginx \
	&& printf "\n" | pecl install mongo-1.6.7 \
	&& echo extension=mongo.so > /etc/php/5.6/mods-available/mongo.ini \
	&& phpenmod mongo \
# Clone source and build with composer
	&& composer install --no-interaction \
# Use local mongodb
	&& sed -i 's@mongo:27017@127.0.0.1:27017@' app/config/parameters.yml \
# Start mongodb, create database and load fixtures
	&& service mongodb start \
	&& bin/console doctrine:mongodb:schema:update \
	&& bin/console mongodb:migrations:migrate --no-interaction \
	&& bin/console doctrine:mongodb:fixtures:load --no-interaction \
# Clean up installed composer packages
	&& SYMFONY_ENV=prod composer install --no-dev --classmap-authoritative --no-interaction \
	&& chown -R www-data:www-data /app

ENV SYMFONY_ENV=prod

COPY .docker/standalone/nginx-site.conf /etc/nginx/sites-enabled/default
COPY .docker/standalone/entrypoint.sh /etc/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["sh", "/etc/entrypoint.sh"]
