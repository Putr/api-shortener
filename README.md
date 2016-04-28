Simple URL shortener API
------------------------

This is a simple shortener with **no GUI**, just an API & one YAML config file.

[API documentation](doc/api.md)

**Features**
- Multiple domains per install
- Per domain create/delete access controll
- Append GET paramateres to resolved URLs (configured per domain)
- Can short log url hits to log file
- Counts number of hits, grouped by days

## Dependencies

- Redis
- Nginx
- PHP 5.5+
- Composer

## Install dependencies

**Redis**

If using in production, make sure to secure the install correctly!

[Download an install from official page](http://redis.io/download)

**Composer**

[Official page](https://getcomposer.org/download/)


## Deploy

**Run Composer**

    composer install

**Setup permissions**

    sudo chown [USER]:www-data log/ -R
    sudo chmod 775 log -R

**Configure nginx**

Copy the distribution file to create a local version

    cp  etc/nginx.confg.dist etc/nginx.conf

Configure it to your needs (specifically 'server_name' and path to php socket)

Restart nginx after you are done.

**Configure**

If deploying a development install copy the dev version:

    cp config.yml.dist.dev config.yml

For production use:

    cp config.yml.dist.prod config.yml

Add your own access codes and domains.

**IMPORTANT**: Access codes in the file must be sha1 hashed!

**Note**: If you change the config after you are already using the system you must purge the cache in reddis with:

    php console/clear_config.php

That's it!

## Development

For testing I'm using [Codeception](http://codeception.com/quickstart).

After installing run tests with:

    codecept run --steps