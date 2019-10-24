FROM itkdev/php5.6-fpm

RUN apt-get update --yes \
	&& apt-get install --yes \
	php-pear \
	php5.6-dev \
	libssl-dev \
	&& printf "\n" | pecl install mongo-1.6.7 \
	&& echo extension=mongo.so > /etc/php/5.6/mods-available/mongo.ini \
	&& phpenmod mongo

EXPOSE 9000

CMD [ "docker-entrypoint.sh" ]
