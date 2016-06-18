<?php namespace Titan\Tests\Event;

use Titan\Event\Event;
use Titan\Event\EventInterface;
use Titan\Event\EventListener;
use Titan\Event\EventListenerInterface;
use Titan\Event\EventManager;
use Titan\Event\Exception\InvalidArgumentException;
use Titan\Tests\Common\TestCase;
use Titan\Event\Exception\EventNotFoundException;

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

    private function getEventInstance()
    {
        return new Event();
    }

    public function testGetSetEvents()
    {
        $em = $this->getInstance();
        $this->assertEquals([], $this->invokeProperty($em, 'events'));
        $events = ['something'];
        $em->setEvents($events);
        $this->assertEquals($events, $em->getEvents());
    }

    public function testGetSetListeners()
    {
        $em = $this->getInstance();
        $this->assertEquals([], $this->invokeProperty($em, 'listeners'));
        $listeners = ['something'];
        $em->setListeners($listeners);
        $this->assertEquals($listeners, $em->getListeners());
    }

    public function testBind()
    {
        $name     = 'some_event';
        $listener = new EventListener();

        EventManager::bind($name, $listener);
        $this->assertEquals([$name => [$listener]], EventManager::getInstance()->getListeners());
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
        $this->assertEquals([$name => [$listener]], $em->getListeners());
        $this->invokeMethod($em, 'unbindEvent', [$name]);
        $this->assertEquals([$name => []], $em->getListeners());
    }

    public function testUnbindEventListener()
    {
        $name     = 'some_event';
        $listener = new EventListener();
        $em       = $this->getInstance();
        EventManager::setInstance($em);

        $em->bind($name, $listener);
        $em->bind($name, $listener);
        $this->assertEquals([
            $name => [
                $listener,
                $listener
            ]
        ], $em->getListeners());
        $this->invokeMethod($em, 'unbindEventListener', [
            $name,
            get_class($listener)
        ]);
        $this->assertEquals([$name => []], $em->getListeners());
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
        $this->assertEquals([
            $name => [
                $listener,
                $anotherListener
            ]
        ], $em->getListeners());
        $this->invokeMethod($em, 'unbindEventListenerByOrderId', [
            $name,
            get_class($listener),
            $orderId
        ]);
        $this->assertEquals([$name => [$listener]], $em->getListeners());
    }

    public function testUnbindEventListenerByOrderIdThrowInvalidArgumentException()
    {
        $orderId         = 999;
        $name            = 'some_event';
        $listener        = new EventListener();
        $anotherListener = new self;
        $em              = $this->getInstance();
        EventManager::setInstance($em);

        $em->setListeners([
            $name => [
                $listener,
                $anotherListener
            ]
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->invokeMethod($em, 'unbindEventListenerByOrderId', [
            $name,
            get_class(),
            $orderId
        ]);
    }

    public function testAddEvent()
    {
        $em = EventManager::getInstance();
        $this->assertEquals([], $em->getEvents());

        $event = $this->getEventInstance();
        EventManager::addEvent($event);
        $this->assertEquals([$event::getName() => $event], $em->getEvents());
    }

    public function testRemoveEvent()
    {
        $em    = EventManager::getInstance();
        $event = $this->getEventInstance();

        EventManager::addEvent($event);
        $em->setEvents([$event::getName() => $event]);

        EventManager::removeEvent($event::getName());
        $this->assertEquals([], $em->getEvents());
    }

    public function testGetSortedListeners()
    {
        $em         = EventManager::getInstance();
        $listener   = new EventListener();
        $listener_1 = clone $listener;
        $listener_2 = clone $listener;

        $listener->setOrderId(30);
        $listener_1->setOrderId(10);
        $listener_2->setOrderId(20);

        $name  = 'some_event';
        $event = new Event();
        $event::setName($name);

        // current order: listener (30) -> listener_1 (10) -> listener_2 (20)
        $em->bind($name, $listener);
        $em->bind($name, $listener_1);
        $em->bind($name, $listener_2);

        // sort ascending, expected result is listener_1 -> listener_2 -> listener
        $this->assertEquals([
            $listener_1,
            $listener_2,
            $listener
        ], $this->invokeMethod($em, 'getSortedListeners', [
            $name,
            true
        ]));

        // sort descending, expected result is listener -> listener_2 -> listener_1
        $this->assertEquals([
            $listener,
            $listener_2,
            $listener_1
        ], $this->invokeMethod($em, 'getSortedListeners', [
            $name,
            false
        ]));
    }

    public function testFire()
    {
        $name  = 'some_event';
        $event = $this->getEventInstance();
        $event->setName($name);
        $em = $this->getMockBuilder(EventManager::class)->setMethods([
            'fireEventClosure',
            'fireEventArray',
            'fireEvent',
            'getEvents'
        ])->getMock();
        $em->expects($this->exactly(3))->method('getEvents')->willReturn([$name => $event]);
        $em->expects($this->once())->method('fireEventClosure');
        $em->expects($this->once())->method('fireEventArray');
        $em->expects($this->once())->method('fireEvent');
        EventManager::setInstance($em);

        $em->fire($name, function ($event) {
        });
        $em->fire($name, ['a' => 'b']);
        $em->fire($name);
    }

    public function testFireEventNotFoundException()
    {
        $this->expectException(EventNotFoundException::class);
        $em = $this->getMockBuilder(EventManager::class)->setMethods(['getEvents'])->getMock();
        $em->expects($this->once())->method('getEvents')->willReturn([]);
        EventManager::setInstance($em);
        $em->fire('some_event');
    }

    public function testFireInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $em = $this->getMockBuilder(EventManager::class)->setMethods(['getEvents'])->getMock();
        $em->expects($this->once())->method('getEvents')->willReturn(['some_event' => new self]);
        EventManager::setInstance($em);
        $em->fire('some_event');
    }

    public function testFireInvalidArgumentExceptionToRunner()
    {
        $name  = 'some_event';
        $event = $this->getEventInstance();
        $event->setName($name);
        $em = $this->getMockBuilder(EventManager::class)->setMethods([
            'getEvents'
        ])->getMock();
        $em->expects($this->once())->method('getEvents')->willReturn([$name => $event]);
        EventManager::setInstance($em);

        $this->expectException(InvalidArgumentException::class);
        $em->fire($name, 5);
    }

    public function testFireEvent()
    {
        $name  = 'some_event';
        $event = $this->getMockBuilder(Event::class)->setMethods([
            'stop',
            'isStopped'
        ])->getMock();
        $event->expects($this->once())->method('stop');
        $event->expects($this->once())->method('isStopped')->willReturn(true);
        $event->setName($name);

        $listener = $this->getMockBuilder(EventListener::class)->setMethods(['run'])->getMock();
        $listener->expects($this->once())->method('run')->with($this->equalTo($event))->willReturn(false);

        $em = $this->getMockBuilder(EventManager::class)->setMethods(['getSortedListeners'])->getMock();
        $em->expects($this->once())->method('getSortedListeners')->with($this->equalTo($name))->willReturn([$listener]);
        EventManager::setInstance($em);

        $this->invokeMethod($em, 'fireEvent', [$event]);
    }

    public function testFireEventClosure()
    {
        $event = $this->getEventInstance();
        $eventAfter = clone $event;
        $eventAfter->set('some-key', 'some-value');
        $em    = $this->getMockBuilder(EventManager::class)->setMethods(['fireEvent'])->getMock();
        $em->expects($this->once())->method('fireEvent')->with($this->equalTo($eventAfter));
        EventManager::setInstance($em);

        $this->invokeMethod($em, 'fireEventClosure', [$event, function($event) use ($eventAfter) {
            return $eventAfter;
        }]);
    }

    public function testFireEventArray()
    {
        $event = $this->getEventInstance();
        $eventAfter = clone $event;
        $eventAfter->set('some-key', 'some-value');
        $em    = $this->getMockBuilder(EventManager::class)->setMethods(['fireEvent'])->getMock();
        $em->expects($this->once())->method('fireEvent')->with($this->equalTo($eventAfter));
        EventManager::setInstance($em);

        $this->invokeMethod($em, 'fireEventArray', [$event, ['some-key' => 'some-value']]);
    }
}
