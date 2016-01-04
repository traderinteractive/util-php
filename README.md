# util-php
[![Build Status](http://img.shields.io/travis/dominionenterprises/util-php.svg?style=flat)](https://travis-ci.org/dominionenterprises/util-php)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/dominionenterprises/util-php.svg?style=flat)](https://scrutinizer-ci.com/g/dominionenterprises/util-php/)
[![Code Coverage](http://img.shields.io/coveralls/dominionenterprises/util-php.svg?style=flat)](https://coveralls.io/r/dominionenterprises/util-php)

[![Latest Stable Version](http://img.shields.io/packagist/v/dominionenterprises/util.svg?style=flat)](https://packagist.org/packages/dominionenterprises/util)
[![Total Downloads](http://img.shields.io/packagist/dt/dominionenterprises/util.svg?style=flat)](https://packagist.org/packages/dominionenterprises/util)
[![License](http://img.shields.io/packagist/l/dominionenterprises/util.svg?style=flat)](https://packagist.org/packages/dominionenterprises/util)

A collection of general utilities for making common programming tasks easier.

## Requirements

util-php requires PHP 5.4 (or later).

##Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`dominionenterprises/util` to your project's `composer.json` file such as:

```json
{
    "require": {
        "dominionenterprises/util": "~1.0"
    }
}
```
## Components

This package is a partial metapackage aggregating the following components:

* [dominionenterprises/util-arrays](https://github.com/dominionenterprises/util-arrays-php)
* [dominionenterprises/util-file](https://github.com/dominionenterprises/util-file-php)
* [dominionenterprises/util-http](https://github.com/dominionenterprises/util-http-php)
* [dominionenterprises/util-time](https://github.com/dominionenterprises/util-time-php)
* [dominionenterprises/util-string](https://github.com/dominionenterprises/util-string-php)

##Documentation
Found in the [source](src) itself, take a look!

##Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/dominionenterprises/util-php/pulls)
 * [Issues](https://github.com/dominionenterprises/util-php/issues)

##Project Build
With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```sh
./build.php
```

There is also a [docker](http://www.docker.com/)-based
[fig](http://www.fig.sh/) configuration that will execute the build inside a
docker container.  This is an easy way to build the application:
```sh
fig run build
```

For more information on our build process, read through out our [Contribution Guidelines](CONTRIBUTING.md).
