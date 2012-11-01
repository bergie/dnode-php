<?php
namespace DNode;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function expectCallableOnce()
    {
        $callable = $this->getMock('DNode\CallableStub');
        $callable
            ->expects($this->once())
            ->method('__invoke');

        return $callable;
    }

    protected function expectCallableOnceWithArg($arg)
    {
        $callable = $this->getMock('DNode\CallableStub');
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($arg);

        return $callable;
    }
}
