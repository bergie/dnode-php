<?php

// script to check for memory leaks

require __DIR__.'/../../../vendor/autoload.php';

$loop = new React\EventLoop\StreamSelectLoop();

$i = 0;

$loop->addPeriodicTimer(0.001, function () use ($loop, &$i) {
    $i++;

    $client = new DNode\DNode($loop);
    $client->connect(7070, function ($remote, $conn) {
        $remote->zing(33, function ($n) use ($conn) {
            $conn->end();
        });
    });
});

$loop->addPeriodicTimer(2, function () use (&$i) {
    $kmem = memory_get_usage(true) / 1024;
    echo "Run: $i\n";
    echo "Memory: $kmem KiB\n";
});

$loop->run();
