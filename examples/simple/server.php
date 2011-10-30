<?php
// Include Composer-generated autoloader
require(__DIR__.'/../../vendor/.composer/autoload.php');

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

// Create a DNode server
$server = new DNode\DNode(new Zinger());
$server->listen(7070);
