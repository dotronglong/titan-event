<?php namespace Titan\Event;

interface EventListenerInterface
{
    const ORDER_DEFAULT = 10;
    const ORDER_LOWEST  = 1;

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
     * Event is fired to listener, and ready to run
     *
     * @param EventInterface $event
     * @return bool false to stop event, skip result if otherwise
     */
    public function run(EventInterface $event);
}
