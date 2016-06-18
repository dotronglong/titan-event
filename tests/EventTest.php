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
        $name = 'some_event';
        Event::setName($name);
        $this->assertEquals($name, Event::getName());
    }

    public function testStop()
    {
        $event = $this->getInstance();
        $this->assertFalse($this->invokeProperty($event, 'isStopped'));

        $event->stop();
        $this->assertTrue($this->invokeProperty($event, 'isStopped'));
    }

    public function testIsStopped()
    {
        $event = $this->getInstance();
        $this->assertFalse($event->isStopped());

        $this->invokeProperty($event, 'isStopped', true);
        $this->assertTrue($event->isStopped());
    }
}
