FROM php:7.0-cli
MAINTAINER Andreas Åkre Solberg <andreas.solberg@uninett.no>
# RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -q -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" -y \
#   ca-certificates curl git openssh-client php5-cli php5-mcrypt python

# Install packages
RUN apt-get update && apt-get install -y --no-install-recommends \
  ca-certificates \
  curl \
  cmake \
  g++ \
  git \
  libgmp-dev \
  libpcre3-dev \
  libssl-dev \
  libuv-dev \
  locales \
  make \
  libfreetype6-dev libjpeg62-turbo-dev libpng12-dev \
  libmcrypt-dev \
  && rm -rf /var/lib/apt/lists/*

RUN git clone https://github.com/datastax/php-driver.git /tmp/php-driver && \
  cd /tmp/php-driver && \
  git checkout v1.2.2 && \
  git submodule update --init && \
  cd ext && \
  ./install.sh && \
  cd / && \
  rm -rf /tmp/php-driver

RUN docker-php-ext-install -j$(nproc) iconv mcrypt && \
    docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-install -j$(nproc) gd
# RUN echo 'extension=mcrypt.so'   >  /usr/local/etc/php/conf.d/php-ext-mcrypt.ini
RUN echo 'extension=cassandra.so' > /usr/local/etc/php/conf.d/php-ext-cassandra.ini

COPY etc/php.ini /usr/local/etc/php/

RUN mkdir -p /metadata-import && mkdir -p /metadata-import/etc
WORKDIR /metadata-import
COPY composer.json /metadata-import/

RUN curl https://getcomposer.org/composer.phar > /metadata-import/composer.phar
RUN php --ini
RUN cd /metadata-import && php ./composer.phar install --no-dev

COPY bin /metadata-import/bin
COPY lib /metadata-import/lib
COPY etc/config.php /metadata-import/etc/config.php


CMD ["/metadata-import/bin/getmetadata.php"]
