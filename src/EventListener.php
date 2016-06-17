<?php namespace Titan\Event;

class EventListener implements EventListenerInterface
{
    /**
     * @var EventInterface
     */
    private $event;

    /**
     * @var int
     */
    protected $orderId = self::ORDER_DEFAULT;

    /**
     * @return EventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param EventInterface $event
     */
    public function setEvent(EventInterface $event)
    {
        $this->event = $event;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function run(EventInterface $event)
    {
    }
}
