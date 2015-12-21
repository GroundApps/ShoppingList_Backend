#!/bin/sh

SL_ROOT="/shoppinglist"

if [ -z "$API_KEY" ]; then
    echo "You must define an API_KEY when running this container" >&2
    echo "Use for example: -e \"API_KEY=mysecretpassword\""       >&2
    exit 1
fi

# Check initialisation state
if [ -e "$SL_ROOT/INSTALL.php" ]; then

    # Hash the key and add it to config.php
    hashed_apikey="$( php "$SL_ROOT/docker/hash_apikey.php" "$API_KEY" )"

    sed "s%authKey *= *.*;%authKey = '$hashed_apikey';%" "$SL_ROOT/docker/config_sqlite.php" \
        > "$SL_ROOT/config.php"

    # No need for web install
    rm "$SL_ROOT/INSTALL.php"
fi


# Run PHP-FPM
php-fpm

# Run nginx
nginx
