<?php namespace Titan\Event;

use Titan\Common\BagTrait;

class Event implements EventInterface
{
    use BagTrait;

    /**
     * @var string
     */
    private static $name;

    /**
     * @var bool
     */
    private $isStopped = false;

    /**
     * @return string
     */
    public static function getName()
    {
        return self::$name;
    }

    /**
     * @param string $name
     */
    public static function setName($name)
    {
        self::$name = $name;
    }

    public function isStopped()
    {
        return $this->isStopped;
    }

    public function stop()
    {
        $this->isStopped = true;
    }
}
