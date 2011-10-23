<?php
// Include standard autoloader
require(__DIR__.'/../autoloader.php');

class Zinger
{
    public function zing($n, $cb)
    {
        $cb($n * 100);
    }
}

// Create a DNode server
$server = new DNode\DNode(new Zinger());
$server->listen(7070);
