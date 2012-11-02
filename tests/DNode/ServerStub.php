<?php
namespace DNode;
use Evenement\EventEmitter;
use React\Socket\ServerInterface;

class ServerStub extends EventEmitter implements ServerInterface
{
    public function listen($port, $host = '127.0.0.1')
    {
    }

    public function getPort()
    {
    }

    public function shutdown()
    {
    }
}
