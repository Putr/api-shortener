Simple URL shortener API
------------------------

This is a simple shortener with **no GUI**, just an API. Currently only supports one short domain per install. This will be added soon.

# Dependencies

- Redis
- PHP 5.5+
- Composer

# Install dependencies

## Install and configure redis

If using in production, make sure to secure the install correctly!

[Download an install from official page](http://redis.io/download)

## Install composer

[Official page](https://getcomposer.org/download/)


# Deploy

## Run composer

    composer install

## Setup permissions

    sudo chown [USER]:www-data log/ -R
    sudo chmod 775 log -R

## Configure nginx

Copy the distribution file to create a local version

    cp  etc/nginx.confg.dist etc/nginx.conf

Configure it to your needs (specifically 'server_name' and path to php socket)

Restart nginx after you are done.

## Configure access

If deploying a development install copy the dev version

    cp access.yml.dist.dev access.yml

For production use:

    cp access.yml.dist.prod access.yml

Add your own access codes.

**IMPORTANT**: Access codes in the file must be sha1 hashed!

**Note**: If you change the config after you are already using the system you must purge the cache in reddis with:

    php console/clear_config.php

That's it!

# Development

For testing [Codeception](http://codeception.com/quickstart) is used.

After installing run tests with:

    codecept run --steps