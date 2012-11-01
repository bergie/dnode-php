<?php
namespace DNode;
use React\EventLoop\StreamSelectLoop;

class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers DNode\DNode::__construct
     * @covers DNode\DNode::connect
     * @covers DNode\DNode::listen
     * @test
     */
    public function transformerShouldRespondCorrectly()
    {
        $captured = null;

        $loop = new StreamSelectLoop();

        $server = new DNode($loop, new Transformer());
        $socket = $server->listen(5004);

        $client = new DNode($loop);
        $client->connect(5004, function ($remote, $conn) use (&$captured, $socket) {
            $remote->transform('fou', function ($transformed) use ($conn, &$captured, $socket) {
                $captured = $transformed;
                $conn->end();
                $socket->shutdown();
            });
        });

        $loop->run();

        $this->assertSame('FOO', $captured);
    }
}
