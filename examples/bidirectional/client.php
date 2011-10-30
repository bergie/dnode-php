<?php
// Include Composer-generated autoloader
require(__DIR__.'/../../vendor/.composer/autoload.php');

// This is the class we're exposing to DNode
class Temp
{
    // Compute the client's temperature and stuff that value into the callback
    public function temperature($cb)
    {
        $degC = 20;
        echo "{$degC}° C\n";
        $cb($degC);
    }
}

$dnode = new DNode\DNode(new Temp());
$dnode->connect(6060, function($remote, $connection) {
    $remote->clientTempF(function($degF) use ($connection) {
        echo "{$degF}° F\n";
        $connection->end();
    });
});
