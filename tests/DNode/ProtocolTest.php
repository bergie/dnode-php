<?php
namespace DNode;

class ProtocolTest extends TestCase
{
    public function setUp()
    {
        $this->protocol = new Protocol(new Dog());
    }

    /**
     * @test
     * @covers DNode\Protocol::__construct
     * @covers DNode\Protocol::create
     */
    public function createShouldReturnSession()
    {
        $session = $this->protocol->create();
        $this->assertInstanceOf('DNode\Session', $session);
    }

    /**
     * @test
     * @covers DNode\Protocol::destroy
     */
    public function destroyShouldUnsetSession()
    {
        $session = $this->protocol->create();
        $this->protocol->destroy($session->id);
    }

    /**
     * @test
     * @covers DNode\Protocol::end
     */
    public function endShouldCallEndOnAllSessions()
    {
        $sessions = array(
            $this->protocol->create(),
            $this->protocol->create(),
        );

        foreach ($sessions as $session) {
            $session->on('end', $this->expectCallableOnce());
        }

        $this->protocol->end();
    }

    /**
     * @test
     * @covers DNode\Protocol::parseArgs
     * @dataProvider provideParseArgs
     */
    public function parseArgsShouldParseArgsCorrectly($expected, $args)
    {
        $this->assertSame($expected, $this->protocol->parseArgs($args));
    }

    public function provideParseArgs()
    {
        $closure = function () {};
        $server = new ServerStub();

        $obj = new \stdClass();
        $obj->foo = 'bar';
        $obj->baz = 'qux';

        return array(
            'string number becomes port' => array(
                array('port' => '8080'),
                array('8080'),
            ),
            'leading / becomes path' => array(
                array('path' => '/foo'),
                array('/foo'),
            ),
            'string becomes host' => array(
                array('host' => 'foo'),
                array('foo'),
            ),
            'integer becomes port' => array(
                array('port' => 8080),
                array(8080),
            ),
            'Closure becomes block' => array(
                array('block' => $closure),
                array($closure),
            ),
            'ServerInterface becomes server' => array(
                array('server' => $server),
                array($server),
            ),
            'random object becomes key => val' => array(
                array('foo' => 'bar', 'baz' => 'qux'),
                array($obj),
            ),
        );
    }

    /**
     * @test
     * @covers DNode\Protocol::parseArgs
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Not sure what to do about array arguments
     */
    public function parseArgsShouldRejectInvalidArgs()
    {
        $args = array(array('wat'));
        $this->protocol->parseArgs($args);
    }
}
