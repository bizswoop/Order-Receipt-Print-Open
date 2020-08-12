<?php
namespace Zprint\Aspect;

defined('ABSPATH') or die('No script kiddies please!');

if(!defined('\Zprint\ASPECT_PREFIX')) {
	define('Zprint\ASPECT_PREFIX', '');
}

abstract class Base
{
    protected $name;
    public $args = array();
    public $labels = array();
    public $attaches = array();

    protected $storageScope;
    protected $storageInstance = null;

    /**
     * @param $label_name
     * @param $key
     */
    public function __construct($label_name, $key = null)
    {
        if ($key === null) {
            $key = substr(md5($label_name), 0, 5);
        }

        $this->grey_name = $key;
        $this->name = self::generateName($key);
        $this->args['labels'] = &$this->labels;
        /* Creating Label Using Translating */
        $singular_name = ucwords($label_name);
        $multi_name = $singular_name . 's';
        $this->labels['singular_name'] = __($singular_name);
        $this->labels['name'] = __($multi_name);

        $this->storageScope = InstanceStorage::getCurrentStorage();

        InstanceStorage::getCurrentStorage()->add($this->name, $this);

        static::init();
    }

    public function scope($callback)
    {
        if ($this->storageInstance === null) {
            $this->storageInstance = new InstanceStorage($this->name);
        }

        $that = $this;

        return $this->storageInstance->asCurrentStorage(function () use ($callback, $that) {
            return call_user_func($callback, $that);
        });
    }

    protected static function generateName($name)
    {
        $name = array(InstanceStorage::getCurrentStorage()->getName(), esc_attr(str_replace(' ', '_', $name)));
        $name = array_filter($name);
        $name = implode('_', $name);

        return $name;
    }

    protected static function init()
    {
        static $initialized = false;
        if (!$initialized) {
            // do some

            $initialized = true;
        }
    }

    public function __toString()
    {
        return self::getName($this);
    }

    /**
     * @param $name
     * @param $key
     * @return static
     * @throws \Exception
     */
    public static function get($name, $key = null)
    {
        if ($key === null) {
            $key = substr(md5($name), 0, 5);
        }

        $key = self::generateName($key);
        $storage = InstanceStorage::getCurrentStorage();

        if ($storage->has($key)) {
            $object = $storage->get($key);
            return $object;
        }
        throw new \Exception(get_called_class() . ' with ' . $name . ' not found');
    }

    public static function set($name)
    {
        return new static($name);
    }

    /**
     * @return static
     */
    public function setArgument($args, $data = true)
    {
        if (is_array($args)) {
            $this->args = array_merge($this->args, $args);
        } elseif (is_string($args)) {
            $this->args[$args] = $data;
        }
        return $this;
    }

    public function unsetArgument($name)
    {
        if (isset($this->args[$name])) {
            unset($this->args[$name]);
        }
        return $this;
    }

    public function setLabel($args, $data)
    {
        if (is_array($args)) {
            $this->labels = array_merge($this->labels, $args);
        } elseif (is_string($args)) {
            $this->labels[$args] = $data;
        }
        return $this;
    }

    public function unsetLabel($name)
    {
        if (isset($this->labels[$name])) {
            unset($this->labels[$name]);
        }
        return $this;
    }

    public function attach()
    {
        $obj = func_get_args();
        $this->attaches = array_merge($this->attaches, $obj);
        return $this;
    }

    public function attachFew(array $obj)
    {
        $this->attaches = array_merge($this->attaches, $obj);
        return $this;
    }

    public function detach()
    {
        $obj = func_get_args();
        $this->attaches = array_diff($this->attaches, $obj);
        return $this;
    }

    public function detachFew(array $obj)
    {
        $this->attaches = array_diff($this->attaches, $obj);
        return $this;
    }

    public function attachTo()
    {
        $objs = func_get_args();
        foreach ($objs as $obj) {
            $obj->attach($this);
        }
        return $this;
    }

    public function detachFrom()
    {
        $objs = func_get_args();
        foreach ($objs as $obj) {
            $obj->detach($this);
        }
        return $this;
    }

    public static function getName()
    {
        $args = func_get_args();
        $name = \Zprint\ASPECT_PREFIX;
        foreach ($args as $arg) {
            if (!is_object($arg)) throw new \Exception(strval($arg) . ' must be Aspect Object');
            if ($name) {
                $name .= '_' . $arg->name;
            } else {
                $name .= $arg->name;
            }
        }
        return $name;
    }

    /**
     * @return static[]
     */
    public static function createFew()
    {
        $arr = func_get_args();
        $return = array();
        foreach ($arr as $name => $args) {
            if (is_array($args)) {
                $obj = new static($name);
                $obj->args = array_merge($obj->args, $args);
            } else {
                $obj = new static($args);
            }
            $return[] = $obj;
        }
        return $return;
    }

    /**
     * @return static[]
     */
    public static function getFew()
    {
        $arr = func_get_args();
        $return = array();
        foreach ($arr as $name => $args) {
            if (is_array($args)) {
                $obj = static::get($name);
                $obj->args = array_merge($obj->args, $args);
            } else {
                $obj = static::get($args);
            }
            $return[] = $obj;
        }
        return $return;
    }

    public static function filter_array(&$el)
    {
        if (is_string($el))
            $el = sanitize_text_field($el);
        if (is_array($el)) {
            array_walk($el, array('static', 'filter_array'));
            $el = array_filter($el);
        }
    }

    public function getOrigin($args = array())
    {
        static $number = 0;
        $name = static::getName($this) . $number++;
        $origin = new Origin($name);
        $origin
            ->setArgument($args);
        return $origin;
    }
}
