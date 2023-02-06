FROM composer:2.5.1 as composer

########################################

FROM php:7.4.12-apache AS prod

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        git=1:2.20.1-2+deb10u7 \
        libghc-gnuidn-dev=0.2.2-7+b1 \
        locales=2.28-10+deb10u2 \
        perl=5.28.1-6+deb10u1 \
        python3=3.7.3-1 \
        python3-pip=18.1-5 \
        unzip=6.0-23+deb10u3 \
        zlib1g-dev=1:1.2.11.dfsg-1+deb10u2 \
 && pip3 install docutils==0.14 \
 && rm -rf /var/lib/apt/lists/* \
 && python3 --version \
 && echo en_US.UTF-8 UTF-8 > /etc/locale.gen \
 && locale-gen \
 && locale -a

ENV LANG=en_US.UTF-8

WORKDIR /var/www

ENV PATH /var/www/node_modules/.bin:$PATH
COPY package* /var/www/

RUN (curl -sL https://deb.nodesource.com/setup_19.x | bash) \
 && apt-get update \
 && apt-get install -y --no-install-recommends \
        nodejs=19.6.0-deb-1nodesource1 \
 && rm -rf /var/lib/apt/lists/* \
 && npm install

COPY Gemfile* /var/www/

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        libssl-dev \
        libyaml-dev \
 && rm -rf /var/lib/apt/lists/* \
 && curl -fsSL https://github.com/rbenv/rbenv-installer/raw/HEAD/bin/rbenv-installer | bash \
 && eval "$(~/.rbenv/bin/rbenv init -)" \
 && rbenv install 3.2.0 \
 && rbenv global 3.2.0 \
 && ruby -v \
 && gem install bundler -v 2.3.26 \
 && bundle config set no-cache 'true' \
 && bundler install  --jobs $(($(nproc) * 2))

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY composer.* /var/www/

RUN composer install --no-scripts --no-dev

COPY . /var/www

ENTRYPOINT /var/www/entrypoint

########################################

FROM prod AS dev

RUN composer install --no-scripts \
 && npm install --dev
