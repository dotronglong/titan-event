<?php namespace Titan\Event;

use Titan\Common\BagInterface;

interface EventInterface extends BagInterface
{
    /**
     * Get event name
     *
     * @return string
     */
    public static function getName();

    /**
     * Set event name
     * @param string $name
     */
    public static function setName($name);

    /**
     * Check if event is stopped
     *
     * @return bool
     */
    public function isStopped();

    /**
     * Stop running event
     */
    public function stop();
}
