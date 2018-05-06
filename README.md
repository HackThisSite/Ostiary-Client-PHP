# Ostiary Client (PHP library)

This library provides a PHP client for the [Ostiary session manager](https://github.com/HackThisSite/Ostiary).

[![Build Status](https://travis-ci.org/HackThisSite/Ostiary-Client-PHP.svg?branch=master)](https://travis-ci.org/HackThisSite/Ostiary-Client-PHP)
[![Dependency Status](https://www.versioneye.com/user/projects/5aedd7f40fb24f54307a4767/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/5aedd7f40fb24f54307a4767)

**Note**: This library can be used stand-alone from an [Ostiary server](https://github.com/HackThisSite/Ostiary), or can directly interact with the Redis backend of an Ostiary server. However, doing this grants full access to the Redis backend and circumvents the access controls the Ostiary server provides.

## Description

[Ostiary](https://github.com/HackThisSite/Ostiary) is a simple session token manager that can be used for a variety of use cases, acting as an authenticated and authorized wrapper around a Redis store. Sessions are identified and validated using JSON Web Tokens, and scoped data buckets offer the additional ability to store session meta-data (such as username, email, timezone, etc.).

This PHP client library is used to either interface with an Ostiary server, or work stand-alone.

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
