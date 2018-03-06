# util-php

[![Build Status](https://travis-ci.org/traderinteractive/util-php.svg?branch=master)](https://travis-ci.org/traderinteractive/util-php)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/traderinteractive/util-php.svg?style=flat)](https://scrutinizer-ci.com/g/traderinteractive/util-php/)
[![Coverage Status](https://coveralls.io/repos/traderinteractive/util-php/badge.svg?branch=master&service=github)](https://coveralls.io/github/traderinteractive/util-php?branch=master)

[![Latest Stable Version](http://img.shields.io/packagist/v/traderinteractive/util-http.svg?style=flat)](https://packagist.org/packages/traderinteractive/util-http)
[![Total Downloads](http://img.shields.io/packagist/dt/traderinteractive/util-http.svg?style=flat)](https://packagist.org/packages/traderinteractive/util-http)
[![License](http://img.shields.io/packagist/l/traderinteractive/util-http.svg?style=flat)](https://packagist.org/packages/traderinteractive/util-http)

A collection of general utilities for making common programming tasks easier.

## Requirements

util-php requires PHP 7.0 (or later).

## Composer

To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`traderinteractive/util` to your project's `composer.json` file such as:

```sh
composer require traderinteractive/util-php
```

## Components

This package is a partial metapackage aggregating the following components:

* [traderinteractive/util-arrays](https://github.com/traderinteractive/util-arrays-php)
* [traderinteractive/util-file](https://github.com/traderinteractive/util-file-php)
* [traderinteractive/util-http](https://github.com/traderinteractive/util-http-php)
* [traderinteractive/util-time](https://github.com/traderinteractive/util-time-php)
* [traderinteractive/util-string](https://github.com/traderinteractive/util-string-php)

## Documentation

Found in the [source](src) itself, take a look!

## Contact

Developers may be contacted at:

 * [Pull Requests](https://github.com/traderinteractive/util-php/pulls)
 * [Issues](https://github.com/traderinteractive/util-php/issues)

## Project Build

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
