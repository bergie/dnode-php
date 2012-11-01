<?php
namespace DNode;

class SessionTest extends TestCase
{
    /** @test */
    public function startShouldSendWrapperMethodsToOtherParty()
    {
        $dog = new Dog();
        $session = new Session(0, $dog);

        $expected = array(
            'method'    => 'methods',
            'arguments' => array($dog),
            'callbacks' => array(
                array(0, 'bark'),
                array(0, 'meow'),
            ),
            'links'     => array(),
        );
        $session->on('request', $this->expectCallableOnceWithArg($expected));
        $session->start();
    }

    /** @test */
    public function endShouldEmitEndEvent()
    {
        $dog = new Dog();
        $session = new Session(0, $dog);

        $session->on('end', $this->expectCallableOnce());
        $session->end();
    }
}
