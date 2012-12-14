<?php

// script to check for memory leaks

require __DIR__.'/../../../vendor/autoload.php';

$loop = new React\EventLoop\StreamSelectLoop();

class Zinger
{
    public $i = 0;

    public function zing($n, $callback)
    {
        $this->i++;
        $callback($n * 100);
    }
}

$zinger = new Zinger();

$server = new DNode\DNode($loop, $zinger);
$server->listen(7070);

$loop->addPeriodicTimer(2, function () use ($zinger) {
    $kmem = memory_get_usage(true) / 1024;
    echo "Run: {$zinger->i}\n";
    echo "Memory: $kmem KiB\n";
});

$loop->run();
