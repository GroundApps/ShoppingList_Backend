FROM alpine

RUN    apk update              \
    && apk add                 \
           nginx               \
           php-fpm             \
           php-json            \
    && rm -rf /var/cache/apk/*

COPY .                       /shoppinglist

RUN chown -R nginx:www-data /shoppinglist/

# Copy scripts
COPY docker/nginx.conf      /etc/nginx/
COPY docker/php-fpm.conf    /etc/php/
COPY docker/entrypoint.sh   /


EXPOSE 80

ENV API_KEY=""

VOLUME [ "/data" ]

CMD [ "/entrypoint.sh" ]
