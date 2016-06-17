<?php namespace Titan\Tests\Event;

use Titan\Event\Event;
use Titan\Tests\Common\TestCase;

class EventTest extends TestCase
{
    private function getInstance()
    {
        return new Event;
    }

    public function testGetSetName()
    {
        $event = $this->getInstance();
        $this->assertNull($this->invokeProperty($event, 'name'));
        $event->setName('hello_world');
        $this->assertEquals('hello_world', $event->getName());
    }
}
