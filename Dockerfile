FROM debian:bullseye-slim

ENV DEBIAN_FRONTEND=noninteractive

ARG PHP_VERSION
ARG COMPOSER_PHAR_URL

RUN apt-get update \
  && apt-get install --no-install-recommends --yes \
    wget \
    gnupg2 \
    ca-certificates \
    lsb-release \
    apt-transport-https \
    unzip \
  && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
  && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list \
  && apt-get update \
  && apt-get install --no-install-recommends --yes \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-dom \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-apcu \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-memcached \
    php${PHP_VERSION}-mysqli \
    php${PHP_VERSION}-yaml \
    php${PHP_VERSION}-zip \
  && rm -rf /var/lib/apt/lists/* \
  # install composer
  && wget -O /bin/composer ${COMPOSER_PHAR_URL} \
  && chmod +x /bin/composer
