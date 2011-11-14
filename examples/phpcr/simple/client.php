<?php
// Include Composer-generated autoloader
require(__DIR__.'/../../vendor/.composer/autoload.php');

// Connect to DNode server running in port 7070 and call Zing with argument 33
$dnode = new DNode\DNode();
$dnode->connect(7070, function($remote, $connection) {
    $remote->execute("default", "getPropertyValue", null, function($n) use ($connection) {
        echo "n = {$n}\n";
        $connection->end();
    });
});
