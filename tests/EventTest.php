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
}
