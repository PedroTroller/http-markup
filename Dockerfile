FROM composer:1.7 as composer

FROM php:7.2-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    git \
    libghc-gnuidn-dev \
    perl \
    python \
    python-pip \
    ruby-dev \
    unzip \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

RUN pip install docutils==0.14

RUN gem install --no-document github-markup -v 2.0.1 && \
    gem install --no-document RedCloth && \
    gem install --no-document asciidoctor && \
    gem install --no-document commonmarker && \
    gem install --no-document creole && \
    gem install --no-document org-ruby && \
    gem install --no-document rdoc -v 3.6.1 && \
    gem install --no-document redcarpet && \
    gem install --no-document wikicloth

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer global require hirak/prestissimo

WORKDIR /var/www/html

COPY composer.* /var/www/html/

RUN composer install --no-scripts --no-dev

COPY . /var/www/html
