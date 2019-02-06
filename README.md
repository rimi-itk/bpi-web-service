BPI Web-service
========

Overview
--------
BPI service is a storage of content provided by libraries. That is BPI is an
danish acronym for `Biblioteks Produceret Indhold` - Library Produced Content.

As the name states this service can be used across libraries to share content
between each other using an unified API.

The service itself follows REST architecture with token based authorisation.

Pre-requisites
--------

1. Web-server with `PHP` version `5.6` with `mongo` extension. Only this
version and it's minor versions are supported with plans to add support
for latest PHP in near future;

2. Mongo database engine version `2.x`. Latest version (at the moment of
writing this text) of mongo `3.x` was not tested. As soon as the service
sources will be updated to `PHP 7.x`, support for mongo `3.x` will
be default and most likely support for mongo versions `2.x` will be abandoned;

3. `composer`;

Installation
--------

The installation procedure assumes that `develop` branch of this repository
is used.

Clone this repository and switch to `develop` branch;
```bash
git clone git@github.com:inleadmedia/rest-api.git bpi-service && cd bpi-service && git checkout develop
```
Install dependencies via `composer` and fill in the prompted values;  
Do **NOT** leave `secret` value `null`, otherwise the process would fail.  
If this happened, remove the `./vendor` and `app/config/parameters.yml` file
and repeat this step again;
```bash
composer install
```

Current configuration is pre-configured for mongo database that is **NOT**
using any authorisation. If mongo setup uses authentication, edit
`app/config/config.yml` file and use the database connection URI string
instead:
```
doctrine_mongodb:
    connections:
        default:
            #server: "%mongodb_server%"
            server: mongodb://%mongodb_user%:%mongodb_pass%@localhost:27017/%mongodb_db%
```

Make sure directory `./web/uploads/assets` exists and is write-able by the
web-server user. This directory holds all the images that get pushed
along the content.

Optionally, run the bundled tests to check the installation:
```bash
./run-tests.sh
```

Note that this file expects a `php5.6` executable to be available in your path.
Feel free to edit this script accordingly. `php5.6` was left due to
compatibility issues when several php versions are installed in the system.

An similar output should follow:
```bash
PHPUnit 5.7.27 by Sebastian Bergmann and contributors.

Testing BpiApiTests
......................                                            22 / 22 (100%)

Time: 1.63 minutes, Memory: 88.25MB

OK (22 tests, 1555 assertions)
```
