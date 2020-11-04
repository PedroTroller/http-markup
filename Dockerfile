FROM composer:2.0.4 as composer

########################################

FROM php:7.4.12-apache AS prod

RUN (curl -sL https://deb.nodesource.com/setup_14.x | bash) \
 && apt-get update \
 && apt-get install -y --no-install-recommends \
        git \
        libghc-gnuidn-dev \
        locales \
        nodejs \
        perl \
        python \
        python-pip \
        ruby-dev \
        unzip \
        zlib1g-dev \
 && pip install docutils==0.14 \
 && gem install bundler \
 && rm -rf /var/lib/apt/lists/*

RUN bundle config set no-cache 'true'

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN echo en_US.UTF-8 UTF-8 > /etc/locale.gen && \
    locale-gen && \
    locale -a

ENV LANG=en_US.UTF-8
ENV PATH /var/www/node_modules/.bin:$PATH

WORKDIR /var/www

COPY Gemfile* /var/www/
COPY composer.* /var/www/
COPY package* /var/www/

RUN bundler install  --jobs $(($(nproc) * 2)) \
 && composer install --no-scripts --no-dev \
 && npm install

COPY . /var/www

########################################

FROM prod AS dev

RUN composer install --no-scripts \
 && npm install --dev
