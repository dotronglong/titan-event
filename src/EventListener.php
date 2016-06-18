<?php namespace Titan\Event;

class EventListener implements EventListenerInterface
{
    /**
     * @var int
     */
    protected $orderId = self::ORDER_DEFAULT;

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
