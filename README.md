### Adimeo Data Suite

Adimeo Data Suite (ADS) provides tools to:
- Easily manage an Elasticsearch cluster
- Harvest various types of datasources
- Process data before indexing it
- Design search pages
- Etc.

#### Requirements

ADS requires Elasticsearch version 5.x (Download available [here](https://www.elastic.co/downloads/past-releases))

#### Installation

Get the latest version from Github : 
https://github.com/louisicard/adimeo-data-suite/releases/latest

1. Extract form archive then run

```sh
composer update
```

2. Configure .env file

3. Run builtin server

```sh
composer require server --dev
```

Then start server (Search pages won't be working with this command. You should use server:start instead with is multithreaded but requires pcntl extension for PHP)

```sh
php bin/console server:run --env =prod
```

4. Or configure your favorite webserver (See doc on [symfony.org](https://symfony.com/doc/current/setup/web_server_configuration.html))

#### Usage

Login on http://host/login (Default user/password is admin/admin)