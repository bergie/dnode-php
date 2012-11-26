<?php
namespace DNode;
use React\Stream\CompositeStream;

class Stream extends CompositeStream
{
    private $dnode;

    public function __construct(DNode $dnode, Session $client, $onReady)
    {
        $this->dnode = $dnode;

        foreach ($this->dnode->stack as $middleware) {
            call_user_func($middleware, array($client->instance, $client->remote, $client));
        }

        if ($onReady) {
            $client->on('ready', function () use ($client, $onReady) {
                call_user_func($onReady, $client->remote, $client);
            });
        }

        $input = new InputStream($client);
        $output = new OutputStream($client);

        parent::__construct($output, $input);
    }
}
