<?php
// Include Composer-generated autoloader
require(__DIR__.'/../../vendor/autoload.php');

$loop = new React\EventLoop\StreamSelectLoop();

// Connect to DNode server running in port 7070 and call Zing with argument 33
$dnode = new DNode\DNode($loop);
$dnode->connect(7070, function($remote, $connection) {
    $remote->getPropertyValue("default", "/jcr:primaryType", function($val, $exc, $error) use ($connection) {
        echo $val;
        if ($exc != null)
            echo "Exception {$exc} thrown with message {$error} \n";
        $connection->end();
    });
});

$loop->run();
