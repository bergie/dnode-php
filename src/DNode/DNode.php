<?php
namespace DNode;
use Evenement\EventEmitter;

class DNode extends EventEmitter
{
    private $protocol;
    private $stack = array();

    public function __construct($wrapper)
    {
        $this->protocol = new Protocol($wrapper);
    }

    public function using($middleware)
    {
        $this->stack[] = $middleware;
        return $this;
    }

    public function connect()
    {
    }

    public function listen()
    {
    }

    public function end()
    {
        $this->protocol->end();
        $this->emit('end');
    }

    public function close()
    {
        $this->server->close();
    }
}
