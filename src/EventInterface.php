<?php namespace Titan\Event;

use Titan\Common\BagInterface;

interface EventInterface extends BagInterface
{
    /**
     * Get event name
     *
     * @return string
     */
    public function getName();

    /**
     * Set event name
     * @param string $name
     */
    public function setName($name);
}
