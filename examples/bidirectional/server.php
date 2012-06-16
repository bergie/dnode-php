<?php
// Include Composer-generated autoloader
require(__DIR__.'/../../vendor/autoload.php');

// This is the class we're exposing to DNode
class Converter
{
    // Poll the client's own temperature() in celsius and convert that value to
    // fahrenheit in the supplied callback
    public function clientTempF($cb)
    {
        $this->remote->temperature(function($degC) use ($cb) {
            $degF = round($degC * 9 / 5 + 32);
            $cb($degF);
        });
    }
}

$loop = new React\EventLoop\StreamSelectLoop();

// Create a DNode server
$server = new DNode\DNode($loop, new Converter());
$server->listen(6060);

$loop->run();
