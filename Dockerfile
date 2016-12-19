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
  php5-curl \
  php5-dev \
  php5-gmp \
  php5-imagick \
  php5-mcrypt \
  && rm -rf /var/lib/apt/lists/*

RUN git clone https://github.com/datastax/php-driver.git /tmp/php-driver && \
  cd /tmp/php-driver && \
  git checkout v1.2.2 && \
  git submodule update --init && \
  cd ext && \
  ./install.sh && \
  cd / && \
  rm -rf /tmp/php-driver

RUN echo 'extension=cassandra.so' > /usr/local/etc/php/conf.d/php-ext-cassandra.ini
#RUN echo 'extension=cassandra.so' >/etc/php5/apache2/conf.d/cassandra.ini

COPY metadata-import /metadata-import
RUN curl https://getcomposer.org/composer.phar > /metadata-import/composer.phar
RUN cd /metadata-import && php ./composer.phar install --no-dev

WORKDIR /metadata-import

CMD ["/metadata-import/getmetadata.php"]
