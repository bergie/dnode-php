<?php
namespace DNode;
use React\Stream\WritableStream;

class InputStream extends WritableStream
{
    private $client;
    private $buffer = '';

    public function __construct(Session $client)
    {
        $this->client = $client;

        $that = $this;

        $client->on('end', function () use ($that) {
            $that->end();
        });
    }

    public function write($data)
    {
        $this->buffer .= $data;
        if (false !== strpos($this->buffer, "\n")) {
            $commands = explode("\n", $this->buffer);
            $tail = array_pop($commands);

            foreach ($commands as $command) {
                $this->client->parse($command);
            }

            $this->buffer = $tail;
        }
    }

    public function close()
    {
        if ($this->closed) {
            return;
        }

        parent::close();

        $this->client->end();
    }
}
