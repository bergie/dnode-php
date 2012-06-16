<?php
/* Include dependencies */
require(__DIR__.'/../vendor/autoload.php');

class RemoteSessionClient
{
    private $value = null;
    private $error = null;
    private $exception = null;
    private $loop = null;
    private $port = 0;
    private $dnode = null;

    public function __construct ($port)
    {
        $this->loop = new React\EventLoop\StreamSelectLoop();

        $this->port = $port;
        $this->dnode = new DNode\DNode($this->loop, $this);
    }

    public function getPropertyValue($path)
    {
        $this->dnode->connect($this->port, function($remote, $connection) use ($path) {
            /* Get property value from the server */
            $remote->getPropertyValue($path, function() use ($connection) {
                /* Close the connection */
                $connection->end();
            });
        });
        $this->loop->run();

        if ($this->exception != null) {
            $exception = $this->exception;
            $msg = $this->error;
            $this->exception = null;
            $this->error = null;
            throw new $exception($msg);
        }

        return $this->value;
    }

    public function setException($exception, $msg) {
        $this->exception = $exception;
        $this->error = $msg;
    }

    /* Set value */
    public function setValue($a, $cb)
    {
        $this->value = $a;
        $cb();
    }

    public function getValue()
    {
        return $this->value;
    }
}

/*
$crSession = new RemoteSession(6060);
$value = $crSession->getPropertyValue("/jcr:primaryType");
var_dump ($crSession->getException());
var_dump ($crSession->getError());
var_dump ($value);*/
