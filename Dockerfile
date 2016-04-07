FROM alpine

RUN    apk update              \
    && apk add                 \
           nginx               \
           php-fpm             \
           php-json            \
           php-pdo             \
           php-pdo_sqlite      \
    && rm -rf /var/cache/apk/*

COPY .                       /shoppinglist

VOLUME [ "/shoppinglist/data" ]

RUN    chown -R nginx:www-data /shoppinglist/      \
    && chown -R nginx:www-data /shoppinglist/data/

# Copy scripts
COPY docker/nginx.conf      /etc/nginx/
COPY docker/php-fpm.conf    /etc/php/
COPY docker/entrypoint.sh   /


EXPOSE 80

ENV API_KEY=""


CMD [ "/entrypoint.sh" ]
