BPI Web-service
========

```sh
docker build --tag=bpi-web-service .
```

```sh
docker run --interactive --tty --publish 8888:80 bpi-web-service
```

```sh
docker-compose up --detach
docker-compose exec phpfpm composer install

docker-compose exec phpfpm bin/console doctrine:mongodb:schema:update
docker-compose exec phpfpm bin/console mongodb:migrations:migrate --no-interaction

echo http://$(docker-compose port nginx 80)
```

Access the api:

```sh
BPI_TOKEN=$(docker-compose exec phpfpm php -r "echo password_hash('dev'.'dev'.'dev', PASSWORD_BCRYPT);")
curl --header 'Auth: BPI agency="dev", token="'$BPI_TOKEN'"' http://$(docker-compose port nginx 80)/node/collection
```

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
using any authentication. If mongo setup uses authentication, edit
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

Make sure `./var` directory is write-able by the web-server. This is the place
for temporary and cache files used by the core framework.

Point your web-server virtual host to `/web` directory.

Optionally, run the bundled tests to check the installation:
```bash
./run-tests.sh
```

Note that this file expects a `php5.6` executable to be available in your path.
Feel free to edit this script accordingly. `php5.6` was left due to
compatibility issues when several php versions are installed in the system.

A similar output should follow:
```
PHPUnit 5.7.27 by Sebastian Bergmann and contributors.

Testing BpiApiTests
......................                                            22 / 22 (100%)

Time: 1.63 minutes, Memory: 88.25MB

OK (22 tests, 1555 assertions)
```

Currently, there is neither docker container available, nor any virtualized
environment was tested with these sources.

Configuration
--------
The installation is already pre-configured to be ready for usage, only
relevant part might be the security of the administrative interface.  
The username and password for above is located in `app/config/security.yml`
file.
Edit this section accordingly and pick a secure password:
```
security:
    providers:
        admins:
            memory:
                users:
                    admin:
                        password: _YOUR_PASSWORD_HERE_
                        roles: ROLE_ADMIN
                    sa:
                        password: _YOUR_PASSWORD_HERE_
                        roles: ROLE_SUPER_ADMIN

```

Additional security is highly recommended, by limiting the allowed hosts/ip's
to the `/admin` route. This is a system/web-server related setup and is not
covered by BPI sources.

Usage
--------
BPI service access relies on agencies. Agencies are like clients,
each with it's own unique identifier consisting of a six-digit number and
a pair of keys.

Each client must be informed with `Public id`, `Public key` and `Secret`
values to be able to authenticate and use the service.

These three values are used to generate a request token that is validated
in the service and, if validation succeeded, the request is processed.

To create a valid hash generate a `BCRYPT` hash by joining all three values
in a single string and hash it with the appropriate method.

An example implementation of token creation in `PHP`:
```php
$agencyId = '999999';
$publicKey = 'ac582ae9081804f93f2f755140c31200';
$secretKey = 'bf075f515c758875571f21f32c0ff56281c08ed3';

$requestToken = password_hash(
    $agencyId.$publicKey.$secretKey,
    PASSWORD_BCRYPT
);

// Result of the above:
// $2y$10$bDyY7TFlLl5HPgRgbWvLJe8dErlzQpPR0tJXlidfvCmfD7joogSUi
```

To authenticate in the service the agency id and token generated above must
be provided with the request. These should accompany the request payload
either in the query string:
```
http://BPI_DOMAIN/node/collection?_authorization[agency]=999999&_authorization[token]=$2y$10$XwhtwKK9Sau8F/H2Xg0yfuwpsEu.GNv/cJwdrovnxKCJncOXFynym
```
or header (preferably):
```
Auth: BPI agency="999999", token="$2y$10$XwhtwKK9Sau8F/H2Xg0yfuwpsEu.GNv/cJwdrovnxKCJncOXFynym"
```

Exposed endpoints:
--------
`GET /` - Main entry point, returns a generic service schema outlining available
endpoints and their request parameters.  
`GET /node/collection` - Fetched a list of content that is available in the
storage.  
`GET /node/item/{id}` - Fetch data for a single content, identified by it's id.  
`GET /node/syndicate/{id}` - Mark a node as syndicated.  
`GET /node/delete` - Mark a node as deleted.  
`POST /node` - Store a content entry.  
`GET /statistics` - Fetch current service statistic data, i.e. number of pushed
and/or syndicated nodes.  
`GET /profile/dictionary` - Fetch dictionaries known by the service.
Dictionaries are a set of tags that categorize content. Best analogy would
be Drupal taxonomy system.  
`GET|POST /user/*` - TBD.  
`GET|POST /channel/*` - TBD.

For example implementation see: https://github.com/inleadmedia/bpi-client/blob/master/Bpi/Sdk/Bpi.php

All responses are `XML` strings with the respective HTTP codes.

A more detailed documentation about the endpoints, payloads and response codes
would follow in near future using auto-generated documentation processed by
https://github.com/nelmio/NelmioApiDocBundle bundle.

Administration
--------
Administrative interface is available at `/admin` route. Authorisation is
required to access this route. Credentials used are set in the respective
configuration file, see `Configuration` section.

Once authorized, the administrative interface allows management of most
entities that BPI service implements.

To create a new agency (client):  
Authenticate in the administrative interface at `http(s)://BPI_DOMAIN/admin` and
access the `Agencies` link. Create a new agency by clicking the `Add new` link.  
Fill in the `Public id` with a six-digit value, follow with the `Name` and
`Moderator` fields.  
Leave `Internal` checkbox checked.  
`Public key` and `Secret` key fields will be filled automatically by some
random values. Edit them manually for specific values, if needed.  

Contributing
--------
See https://github.com/inleadmedia/rest-api/blob/develop/CONTRIBUTING.md
