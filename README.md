DNode protocol for PHP
======================

This project implements the [DNode](http://substack.net/dnode) remote procedure call protocol for PHP. The intent is to enable PHP scripts to act as part of a distributed Node.js cloud, allowing Node to call PHP code, and PHP to call Node code.

You can read more about DNode and PHP in the [introductory blog post](http://bergie.iki.fi/blog/dnode-make_php_and_node-js_talk_to_each_other/).

[![Build Status](https://secure.travis-ci.org/bergie/dnode-php.png?branch=master)](http://travis-ci.org/bergie/dnode-php)

## Installing

dnode-php can be installed using the [Composer](http://packagist.org/) tool. You can either add `dnode/dnode` to your package dependencies, or if you want to install dnode-php as standalone, go to the main directory of this package and run:

    $ wget http://getcomposer.org/composer.phar
    $ php composer.phar install

You can then use the composer-generated autoloader to access the DNode classes:

    require 'vendor/autoload.php';

## Running the examples

After installing, you can run the DNode examples located in the examples directory. Each example contains both a client and a server.

For example:

    $ php examples/simple/server.php
    $ php examples/simple/client.php
    n = 3300

The examples have been written to be compatible with the [DNode examples](https://github.com/substack/dnode/tree/master/example), meaning that you can use any combination of PHP-to-PHP, Node-to-Node, PHP-to-Node, or Node-to-PHP as you wish.

    $ node simple/client.js
    n = 3300

## Current limitations

* Only regular, non-encrypted TCP sockets are supported

## Development

dnode-php is under heavy development. If you want to participate, please send pull requests.
