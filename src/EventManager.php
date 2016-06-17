<?php namespace Titan\Event;

use Titan\Common\Singleton;
use Titan\Event\Exception\InvalidArgumentException;

class EventManager extends Singleton
{
    /**
     * @var array
     */
    private $events = [];

    /**
     * Get all events
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Set all events
     *
     * @param array $events
     */
    public function setEvents($events)
    {
        $this->events = $events;
    }

    /**
     * Bind a listener to Event Manager
     * @param string                 $name name of event
     * @param EventListenerInterface $listener
     */
    public static function bind($name, EventListenerInterface $listener)
    {
        $em     = static::getInstance();
        $events = $em->getEvents();
        if (!isset($events[$name])) {
            $events[$name] = [];
        }

        $events[$name][] = $listener;
        $em->setEvents($events);
    }

    /**
     * Unbind a listener from Event Manager
     *
     * @param string   $name name of event
     * @param string   $listener class of Listener default is null which unbind all listeners of this event's name
     * @param int|null $orderId unbind listener at specific orderId, default is null which unbind all same type of listener
     * @throws InvalidArgumentException
     */
    public static function unbind($name, $listener = null, $orderId = null)
    {
        $em = static::getInstance();
        if ($listener !== null && $orderId !== null) {
            $em->unbindEventListenerByOrderId($name, $listener, $orderId);
        } elseif ($listener !== null) {
            $em->unbindEventListener($name, $listener);
        } else {
            $em->unbindEvent($name);
        }
    }

    /**
     * Unbind event by name. It should remove all listeners for this name of event
     *
     * @param string $name
     */
    public function unbindEvent($name)
    {
        $events        = $this->getEvents();
        $events[$name] = [];
        $this->setEvents($events);
    }

    /**
     * Unbind event by name and listener class.
     * It should remove all listeners which is bound to this event's name and have appropriate listener class
     *
     * @param string $name
     * @param string $listener
     */
    public function unbindEventListener($name, $listener)
    {
        $events = $this->getEvents();
        if (isset($events[$name]) && count($events[$name])) {
            foreach ($events[$name] as $i => $eventListener) {
                if (get_class($eventListener) === $listener) {
                    unset($events[$name][$i]);
                }
            }
        }
        $this->setEvents($events);
    }

    /**
     * @param string $name
     * @param string $listener
     * @param int    $orderId
     * @throws InvalidArgumentException
     */
    public function unbindEventListenerByOrderId($name, $listener, $orderId)
    {
        $events = $this->getEvents();
        if (isset($events[$name]) && count($events[$name])) {
            foreach ($events[$name] as $i => $eventListener) {
                if (get_class($eventListener) === $listener) {
                    if ($eventListener instanceof EventListenerInterface) {
                        if ($eventListener->getOrderId() === $orderId) {
                            unset($events[$name][$i]);
                        }
                    } else {
                        throw new InvalidArgumentException(get_class($eventListener) . ' must implement EventListenerInterface.');
                    }
                }
            }
        }
        $this->setEvents($events);
    }
}
