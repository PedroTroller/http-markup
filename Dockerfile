FROM composer:2.5.2 as composer

########################################

FROM php:8.2.2-apache AS prod

RUN (curl -sL https://deb.nodesource.com/setup_19.x | bash) \
 && apt-get update \
 && apt-get install -y --no-install-recommends \
        git \
        locales \
        libidn11-dev \
        nodejs \
        perl \
        python3 \
        python3-pip \
        ruby-full \
        unzip \
        zlib1g-dev \
 && pip3 install docutils==0.19 \
 && gem install bundler -v 2.4.6 \
 && rm -rf /var/lib/apt/lists/* \
 && python3 --version \
 && ruby --version \
 && bundle config set no-cache 'true' \
 && echo en_US.UTF-8 UTF-8 > /etc/locale.gen \
 && locale-gen \
 && locale -a

ENV LANG=en_US.UTF-8
ENV PATH /var/www/node_modules/.bin:$PATH

WORKDIR /var/www

COPY --from=composer /usr/bin/composer /usr/bin/composer

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
