<?php namespace Titan\Tests\Event;

use Titan\Event\Event;
use Titan\Event\EventListener;
use Titan\Tests\Common\TestCase;

class EventListenerTest extends TestCase
{
    private function getInstance()
    {
        return new EventListener;
    }

    public function testGetSetOrderId()
    {
        $listener = $this->getInstance();
        $this->assertEquals(EventListener::ORDER_DEFAULT, $this->invokeProperty($listener, 'orderId'));
        $listener->setOrderId(5);
        $this->assertEquals(5, $listener->getOrderId());
    }

    public function testGetSetEvent()
    {
        $listener = $this->getInstance();
        $event    = new Event();
        $this->assertNull($this->invokeProperty($listener, 'event'));
        $listener->setEvent($event);
        $this->assertEquals($event, $listener->getEvent());
    }
}
