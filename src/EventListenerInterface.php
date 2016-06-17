<?php namespace Titan\Event;

interface ListenerInterface
{
    /**
     * Set event
     *
     * @param EventInterface $event
     */
    public function setEvent(EventInterface $event);

    /**
     * Get event
     *
     * @return EventInterface
     */
    public function getEvent();

    /**
     * Get Order Id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Set Order Id
     *
     * @param int $orderId
     */
    public function setOrderId($orderId);
}
