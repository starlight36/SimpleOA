FROM php:apache

# RUN a2enmod rewrite

# install the PHP extensions we need
RUN apt-get update && apt-get install -y libpng12-dev libjpeg-dev && rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr \
	&& docker-php-ext-install gd
RUN docker-php-ext-install mysqli pdo_mysql

VOLUME /var/www/html/
COPY . /var/www/html/
COPY php.ini /usr/local/etc/php/simple-oa.ini


COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# grr, ENTRYPOINT resets CMD now
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
