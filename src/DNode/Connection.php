<?php
namespace DNode;
use React\EventLoop\LoopInterface;
use React\Socket\Connection as BaseConnection;

class Connection extends BaseConnection
{
    private $lastBufferSize;

    public function handleData($stream)
    {
        if ($this->bufferSize != $this->lastBufferSize) {
            $this->adjustReadBufferSize($stream);
        }

        $data = fread($stream, $this->bufferSize);

        if ('' === $data || false === $data) {
            $this->end();
        } else {
            $this->emit('data', array($data, $this));
        }
    }

    protected function adjustReadBufferSize($stream)
    {
        $this->lastBufferSize = $this->bufferSize;
        if (0 !== stream_set_read_buffer($stream, 0)) {
            throw new \RuntimeException('Unable to set read buffer on stream');
        }
    }
}