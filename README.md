DNode protocol for PHP
======================

This project implements the DNode remote procedure call protocol for PHP. The intent is to enable PHP scripts to act as part of a distributed Node.js cloud, allowing Node to call PHP code, and PHP to call Node code.

## Current limitations

* PHP can only act as a client
* Only regular, non-encrypted TCP sockets are supported
* PHP methods cannot yet be exposed to Node, except as method response callbacks

## Performance

Surprisingly, with simple calls PHP is faster as a DNode client than Node.js. Talking to the _simple example_ DNode server from the dnode repository:

    $ time php examples/simple/client.php 
    n = 3300

    real	0m0.067s
    user	0m0.030s
    sys	0m0.030s

The same with a Node.js client:

    $ time node simple/client.js 
    n = 3300

    real	0m0.173s
    user	0m0.140s
    sys	0m0.030s

## Development

dnode-php is under heavy development. If you want to participate, please send pull requests.
