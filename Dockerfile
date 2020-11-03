FROM composer:2.0.4 as composer

########################################

FROM php:7.3.0-apache AS prod

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        git \
        libghc-gnuidn-dev \
        perl \
        python \
        python-pip \
        ruby-dev \
        unzip \
        locales \
        zlib1g-dev \
 && rm -rf /var/lib/apt/lists/*

RUN echo en_US.UTF-8 UTF-8 > /etc/locale.gen && \
    locale-gen && \
    locale -a

ENV LANG=en_US.UTF-8

RUN pip install docutils==0.14

RUN gem install bundler

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.* /var/www/html/
RUN composer install --no-scripts --no-dev

COPY Gemfile* /var/www/html/
RUN bundler install

COPY . /var/www/html

########################################

FROM prod AS dev

RUN composer install --no-scripts --no-dev
