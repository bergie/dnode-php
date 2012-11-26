<?php
namespace DNode;
use React\Stream\ThroughStream;

class LogStream extends ThroughStream
{
    private $prefix;

    public function __construct($prefix)
    {
        parent::__construct();

        $this->prefix = $prefix;
    }

    public function filter($data)
    {
        echo $this->prefix.': '.$data;

        return $data;
    }
}
