FROM php:7.1-cli
WORKDIR /app

RUN apt-get update && apt-get install -y \
        git \
        unzip \
		--no-install-recommends && rm -r /var/lib/apt/lists/*
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php \
  && mv /app/composer.phar /usr/local/bin/composer
RUN pecl install xdebug && docker-php-ext-enable xdebug
