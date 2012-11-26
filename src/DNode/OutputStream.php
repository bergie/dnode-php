<?php
namespace DNode;
use React\Stream\ReadableStream;

class OutputStream extends ReadableStream
{
    public function __construct(Session $client)
    {
        $that = $this;

        $client->on('request', function (array $request) use ($that) {
            $that->emit('data', array(json_encode($request)."\n"));
        });
    }
}
