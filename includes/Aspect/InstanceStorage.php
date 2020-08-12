<?php
namespace Zprint\Aspect;

defined('ABSPATH') or die('No script kiddies please!');

class InstanceStorage
{
    protected static $globalInstance = null;
    protected static $currentInstance = null;

    protected $objects = array();
    protected $name = null;

    public function getName()
    {
        return $this->name;
    }

    public function __construct($name)
    {
        $this->name = $name;
    }

    public static function getGlobalStorage()
    {
        if (self::$globalInstance === null) {
            $global_instance = new self(null);
            self::$globalInstance = $global_instance;
        }

        return self::$globalInstance;
    }

    public function asCurrentStorage($callback)
    {
        $prev_storage = self::getCurrentStorage();
        self::setCurrentStorage($this);
        $result = call_user_func($callback);
        self::setCurrentStorage($prev_storage);
        return $result;
    }

    public static function setCurrentStorage($storage)
    {
        self::$currentInstance = $storage;
    }

    /**
     * @return \Zprint\Aspect\InstanceStorage
     */
    public static function getCurrentStorage()
    {
        $instance = self::$currentInstance;
        if ($instance === null) {
            self::setCurrentStorage(self::getGlobalStorage());
            return self::getCurrentStorage();
        }
        return $instance;
    }

    public function add($name, $object)
    {
        if (isset($this->objects[$name])) trigger_error(get_class($object) . ' with name ' . $object->labels['singular_name'] . ' already exists', E_USER_WARNING);
        $this->objects[$name] = $object;
    }

    public function get($name)
    {
        if (isset($this->objects[$name])) {
            return $this->objects[$name];
        } else {
            return null;
        }
    }

    public function has($name)
    {
        return isset($this->objects[$name]);
    }
}
