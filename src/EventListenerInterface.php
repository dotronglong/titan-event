<?php namespace Titan\Event;

interface EventListenerInterface
{
    const ORDER_DEFAULT = 10;
    const ORDER_LOWEST  = 1;

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

    /**
     * Event is fired to listener
     *
     * @param EventInterface $event
     * @return mixed
     */
    public function run(EventInterface $event);
}
