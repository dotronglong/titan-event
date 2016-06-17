<?php namespace Titan\Tests\Event;

use Titan\Event\EventListener;
use Titan\Event\EventManager;
use Titan\Event\Exception\InvalidArgumentException;
use Titan\Tests\Common\TestCase;

class EventManagerTest extends TestCase
{
    public function setUp()
    {
        EventManager::setInstance(null);
    }

    private function getInstance()
    {
        return new EventManager;
    }

    public function testGetSetEvents()
    {
        $em = $this->getInstance();
        $this->assertEquals([], $this->invokeProperty($em, 'events'));
        $events = ['something'];
        $em->setEvents($events);
        $this->assertEquals($events, $em->getEvents());
    }

    public function testBind()
    {
        $name     = 'some_event';
        $listener = new EventListener();

        EventManager::bind($name, $listener);
        $this->assertEquals([$name => [$listener]], EventManager::getInstance()->getEvents());
    }

    public function testUnbind()
    {
        $name          = 'some_event';
        $listener      = new EventListener();
        $listenerClass = get_class($listener);
        $orderId       = 5;

        $em = $this->getMockBuilder(EventManager::class)->setMethods([
            'unbindEvent',
            'unbindEventListener',
            'unbindEventListenerByOrderId',
        ])->getMock();
        $em->expects($this->once())->method('unbindEvent')->with($this->equalTo($name));
        $em->expects($this->once())->method('unbindEventListener')
            ->with($this->equalTo($name), $this->equalTo($listenerClass));
        $em->expects($this->once())->method('unbindEventListenerByOrderId')
            ->with($this->equalTo($name), $this->equalTo($listenerClass), $this->equalTo($orderId));
        EventManager::setInstance($em);

        EventManager::bind($name, $listener);
        EventManager::unbind($name);

        EventManager::bind($name, $listener);
        EventManager::unbind($name, $listenerClass);

        $listener->setOrderId($orderId);
        EventManager::bind($name, $listener);
        EventManager::unbind($name, $listenerClass, $orderId);
    }

    public function testUnbindEvent()
    {
        $name     = 'some_event';
        $listener = new EventListener();
        $em       = $this->getInstance();
        EventManager::setInstance($em);

        $em->bind($name, $listener);
        $this->assertEquals([$name => [$listener]], $em->getEvents());
        $em->unbindEvent($name);
        $this->assertEquals([$name => []], $em->getEvents());
    }

    public function testUnbindEventListener()
    {
        $name     = 'some_event';
        $listener = new EventListener();
        $em       = $this->getInstance();
        EventManager::setInstance($em);

        $em->bind($name, $listener);
        $em->bind($name, $listener);
        $this->assertEquals([$name => [$listener, $listener]], $em->getEvents());
        $em->unbindEventListener($name, get_class($listener));
        $this->assertEquals([$name => []], $em->getEvents());
    }

    public function testUnbindEventListenerByOrderId()
    {
        $orderId         = 999;
        $name            = 'some_event';
        $listener        = new EventListener();
        $anotherListener = clone $listener;
        $em              = $this->getInstance();
        EventManager::setInstance($em);

        $anotherListener->setOrderId($orderId);
        $em->bind($name, $listener);
        $em->bind($name, $anotherListener);
        $this->assertEquals([$name => [$listener, $anotherListener]], $em->getEvents());
        $em->unbindEventListenerByOrderId($name, get_class($listener), $orderId);
        $this->assertEquals([$name => [$listener]], $em->getEvents());
    }

    public function testUnbindEventListenerByOrderIdThrowInvalidArgumentException()
    {
        $orderId         = 999;
        $name            = 'some_event';
        $listener        = new EventListener();
        $anotherListener = new self;
        $em              = $this->getInstance();
        EventManager::setInstance($em);

        $em->setEvents([$name => [$listener, $anotherListener]]);
        $this->expectException(InvalidArgumentException::class);
        $em->unbindEventListenerByOrderId($name, get_class(), $orderId);
    }
}
