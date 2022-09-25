FROM php:8.1-fpm-alpine

LABEL maintainer="Einar-Johan Hansen"

RUN apk update && apk add supervisor sqlite

RUN mkdir /var/log/supervisor
COPY supervisor/php-app.conf /etc/supervisor/conf.d/php-app.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/php-app.conf"]
