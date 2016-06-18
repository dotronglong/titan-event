<?php namespace Titan\Event;

use Closure;
use Titan\Common\Singleton;
use Titan\Event\Exception\EventNotFoundException;
use Titan\Event\Exception\InvalidArgumentException;

class EventManager extends Singleton
{
    /**
     * @var array
     */
    private $events = [];

    /**
     * @var array
     */
    private $listeners = [];

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
     * Get all listeners
     *
     * @return array
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * Set all listeners
     *
     * @param array $listeners
     */
    public function setListeners($listeners)
    {
        $this->listeners = $listeners;
    }

    /**
     * Bind a listener to Event Manager
     * @param string                 $name name of event
     * @param EventListenerInterface $listener
     */
    public static function bind($name, EventListenerInterface $listener)
    {
        $em        = static::getInstance();
        $listeners = $em->getListeners();
        if (!isset($listeners[$name])) {
            $listeners[$name] = [];
        }

        $listeners[$name][] = $listener;
        $em->setListeners($listeners);
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
    protected function unbindEvent($name)
    {
        $listeners        = $this->getListeners();
        $listeners[$name] = [];
        $this->setListeners($listeners);
    }

    /**
     * Unbind event by name and listener class.
     * It should remove all listeners which is bound to this event's name and have appropriate listener class
     *
     * @param string $name
     * @param string $listener
     */
    protected function unbindEventListener($name, $listener)
    {
        $listeners = $this->getListeners();
        if (isset($listeners[$name]) && count($listeners[$name])) {
            foreach ($listeners[$name] as $i => $eventListener) {
                if (get_class($eventListener) === $listener) {
                    unset($listeners[$name][$i]);
                }
            }
        }
        $this->setListeners($listeners);
    }

    /**
     * @param string $name
     * @param string $listener
     * @param int    $orderId
     * @throws InvalidArgumentException
     */
    protected function unbindEventListenerByOrderId($name, $listener, $orderId)
    {
        $listeners = $this->getListeners();
        if (isset($listeners[$name]) && count($listeners[$name])) {
            foreach ($listeners[$name] as $i => $eventListener) {
                if (get_class($eventListener) === $listener) {
                    if ($eventListener instanceof EventListenerInterface) {
                        if ($eventListener->getOrderId() === $orderId) {
                            unset($listeners[$name][$i]);
                        }
                    } else {
                        throw new InvalidArgumentException(get_class($eventListener) . ' must implement EventListenerInterface.');
                    }
                }
            }
        }
        $this->setListeners($listeners);
    }

    /**
     * Add an event
     *
     * @param EventInterface $event
     */
    public static function addEvent(EventInterface $event)
    {
        $em     = static::getInstance();
        $events = $em->getEvents();

        $events[$event->getName()] = $event;
        $em->setEvents($events);
    }

    /**
     * Remove an event by name
     *
     * @param string $name
     */
    public static function removeEvent($name)
    {
        $em     = static::getInstance();
        $events = $em->getEvents();

        unset($events[$name]);
        $em->setEvents($events);
    }

    /**
     * Fire an event by name
     *
     * @param string             $name name of event
     * @param Closure|array|null $runner Runner might be a closure to do extra actions on event,
     *                                   or simply an array to assign key-value pairs to event
     * @return EventInterface
     * @throws EventNotFoundException
     * @throws InvalidArgumentException
     */
    public static function fire($name, $runner = null)
    {
        $em     = static::getInstance();
        $events = $em->getEvents();
        if (!isset($events[$name])) {
            throw new EventNotFoundException("Event $name could not be found.");
        }

        $event = $events[$name];
        if (!$event instanceof EventInterface) {
            throw new InvalidArgumentException(get_class($event) . ' must implement EventInterface');
        }

        if ($runner) {
            if ($runner instanceof Closure) {
                $em->fireEventClosure($event, $runner);
            } elseif (is_array($runner)) {
                $em->fireEventArray($event, $runner);
            } else {
                throw new InvalidArgumentException("Invalid type of event runner.");
            }
        } else {
            $em->fireEvent($event);
        }

        return $event;
    }

    /**
     * Get sorted listeners by orderId. Use selection sort algorithm
     *
     * @param string $name
     * @param bool   $asc Sort ordering. TRUE if ascending, and FALSE if descending
     * @return array
     */
    protected function getSortedListeners($name, $asc = true)
    {
        $em              = static::getInstance();
        $listeners       = $em->getListeners();
        $sortedListeners = [];
        if (isset($listeners[$name]) && count($listeners[$name])) {
            $sortedListeners = $listeners[$name];
            $totalListeners  = count($sortedListeners);
            for ($i = 0; $i < $totalListeners - 1; $i++) {
                $guard = $i;
                for ($j = $i + 1; $j < $totalListeners; $j++) {
                    $guardListener   = $sortedListeners[$guard];
                    $currentListener = $sortedListeners[$j];
                    if ($guardListener instanceof EventListenerInterface
                        && $currentListener instanceof EventListenerInterface
                    ) {
                        if ($asc && $guardListener->getOrderId() > $currentListener->getOrderId()
                            || !$asc && $guardListener->getOrderId() < $currentListener->getOrderId()
                        ) {
                            $guard = $j;
                        }
                    }
                }

                // swap them
                if ($i !== $guard) {
                    $temporaryListener       = $sortedListeners[$i];
                    $sortedListeners[$i]     = $sortedListeners[$guard];
                    $sortedListeners[$guard] = $temporaryListener;
                }
            }
        }

        return $sortedListeners;
    }

    /**
     * Fire an event
     *
     * @param EventInterface $event
     * @return EventInterface
     */
    protected function fireEvent(EventInterface $event)
    {
        $listeners = $this->getSortedListeners($event->getName());
        foreach ($listeners as $listener) {
            if ($listener instanceof EventListenerInterface) {
                if ($listener->run($event) === false) {
                    $event->stop();
                }
            }

            if ($event->isStopped()) {
                break;
            }
        }

        return $event;
    }

    /**
     * Fire an event which is result of a closure
     *
     * @param EventInterface $event
     * @param Closure        $closure
     * @return EventInterface
     */
    protected function fireEventClosure(EventInterface $event, Closure $closure)
    {
        $event = call_user_func_array($closure, [$event]);

        return $this->fireEvent($event);
    }

    /**
     * Fire an event after setting arguments to event
     *
     * @param EventInterface $event
     * @param array          $arguments
     * @return EventInterface
     */
    protected function fireEventArray(EventInterface $event, array $arguments = [])
    {
        if (count($arguments)) {
            foreach ($arguments as $key => $value) {
                $event->set($key, $value);
            }
        }

        return $this->fireEvent($event);
    }
}
