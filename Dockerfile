FROM composer:1.7 as composer

FROM php:7.2-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    git \
    python \
    python-pip \
    ruby-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN pip install docutils==0.14

RUN gem install \
    RedCloth  \
    asciidoctor  \
    creole  \
    github-markup  \
    org-ruby  \
    rdoc \
    redcarpet

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer global require hirak/prestissimo

WORKDIR /var/www/html

COPY composer.* /var/www/html/

RUN composer install --no-scripts --no-dev

COPY . /var/www/html
