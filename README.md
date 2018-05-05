# Ostiary Client (PHP library)

This library provides a PHP client for the [Ostiary session manager](https://github.com/HackThisSite/Ostiary).

**Note**: This library can be used stand-alone from an [Ostiary server](https://github.com/HackThisSite/Ostiary), or can directly interact with the Redis backend of an Ostiary server. However, doing this grants full access to the Redis backend and circumvents the access controls the Ostiary server provides.

## Installation

Install this library using the [composer](https://getcomposer.org/) PHP package manager:

```sh
composer require "hackthissite/ostiaryclient"
```

For usage, see the documentation section below.

## Documentation

* [Quickstart](doc/) - Overview and quickstart
* [Class API](doc/api/) - Documentation for the end-user classes of the Ostiary PHP client

## Development

Navigate into the directory where you cloned the Git repository. Install dependencies using the [composer](https://getcomposer.org/) PHP package manager:

```sh
composer install
```

### Generating documentation

1. Delete the contents of the `doc/api/` folder
2. Run the command: `vendor/bin/phpdoc-md`
