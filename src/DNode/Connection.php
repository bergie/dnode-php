<?php
namespace DNode;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as BaseConnection;

class Connection extends BaseConnection
{
    private $streamReadFunctionName = 'fread';

    private static $rawIoSchemes = array(
        'tcp',
        'udp'
    );

    public function __construct($stream, LoopInterface $loop, $scheme)
    {
        parent::__construct($stream, $loop);
        if (in_array($scheme, static::$rawIoSchemes)) {
            $this->streamReadFunctionName = 'stream_socket_recvfrom';
        }
    }

    public function handleData($stream)
    {
        $data = call_user_func_array($this->streamReadFunctionName, array($stream, $this->bufferSize));

        if ('' === $data || false === $data) {
            $this->end();
        } else {
            $this->emit('data', array($data, $this));
        }
    }
}