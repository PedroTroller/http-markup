FROM php:7.2-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    git \
    ruby \
    ruby-dev \
    unzip && \
    rm -rf /var/lib/apt/lists/*

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

COPY composer.* /var/www/html/

RUN composer install --no-scripts --no-dev

COPY . /var/www/html
