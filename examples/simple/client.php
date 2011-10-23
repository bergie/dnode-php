<?php
// Include standard autoloader
require(__DIR__.'/../autoloader.php');

// Connect to DNode server running in port 7070 and call Zing with argument 33
$dnode = new DNode\DNode();
$dnode->connect(7070, function($remote, $connection) {
    $method = $remote['zing'];
    $method(33, function($n) use ($connection) {
        echo "n = {$n}\n";
        $connection->end();
    });
});
