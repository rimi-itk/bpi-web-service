FROM ubuntu:16.04

ENV PHP_VERSION 5.6

# Ensure packages are avaiable.
RUN apt-get update

RUN apt-get install -y language-pack-en-base \
    software-properties-common \
    apt-utils \
&& locale-gen en_US.UTF-8 en_DK.UTF-8 en_GB.UTF-8

# Add php repositories
RUN LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php
RUN apt-get update

# Clean up
RUN apt-get remove -y software-properties-common language-pack-en-base \
	&& apt-get autoremove -y

RUN DEBIAN_FRONTEND=noninteractive \
	apt-get install -y \
	php${PHP_VERSION} \
	php${PHP_VERSION}-cli \
	php${PHP_VERSION}-common \
	php${PHP_VERSION}-curl \
	php${PHP_VERSION}-fpm \
	php${PHP_VERSION}-gd \
	php${PHP_VERSION}-json \
	php${PHP_VERSION}-mbstring \
	php${PHP_VERSION}-mcrypt \
	php${PHP_VERSION}-opcache \
	php${PHP_VERSION}-readline \
	php${PHP_VERSION}-soap \
	php${PHP_VERSION}-xml \
	php${PHP_VERSION}-xsl \
	php${PHP_VERSION}-zip \
	php${PHP_VERSION}-xdebug \
	php${PHP_VERSION}-intl \
	php-memcached \
	php-redis \
	php${PHP_VERSION}-dev \
	php-pear \
	unzip \
	git \
	imagemagick \
  nginx \
	&& rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN mkdir /var/run/php/
# COPY .docker/etc/ /etc/

# COPY .docker/nginx.conf /etc/nginx/nginx.conf

# Configure php
RUN sed -i '/memory_limit = 128M/c memory_limit = 256M' /etc/php/${PHP_VERSION}/fpm/php.ini \
	&& sed -i '/;date.timezone =/c date.timezone = Europe\/Copenhagen' /etc/php/${PHP_VERSION}/fpm/php.ini \
	&& sed -i '/upload_max_filesize = 2M/c upload_max_filesize = 16M' /etc/php/${PHP_VERSION}/fpm/php.ini \
	&& sed -i '/post_max_size = 8M/c post_max_size = 20M' /etc/php/${PHP_VERSION}/fpm/php.ini

# Install Mongo 1.5.6
RUN DEBIAN_FRONTEND=noninteractive \
	pecl install mongo-1.6.7
RUN echo extension=mongo.so > /etc/php/5.6/mods-available/mongo.ini
RUN phpenmod mongo

WORKDIR /app

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
	&& php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
	&& php -r "unlink('composer-setup.php');"

RUN git clone --branch feature/docker https://github.com/rimi-itk/bpi-web-service /app

RUN APP_ENV=prod composer install --no-dev --classmap-authoritative

# COPY 

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
