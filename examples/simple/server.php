<?php
// Include Composer-generated autoloader
require(__DIR__.'/../../vendor/autoload.php');

// This is the class we're exposing to DNode
class Zinger
{
    // Public methods are made available to the network
    public function zing($n, $cb)
    {
        // Dnode is async, so we return via callback
        $cb($n * 100);
    }
}

$loop = new React\EventLoop\StreamSelectLoop();

// Create a DNode server
$server = new DNode\DNode($loop, new Zinger());
$server->listen(7070);

$loop->run();
