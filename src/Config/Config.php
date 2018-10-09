<?php
namespace PPP\Config;

class Config
{
    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            throw new \LogicException(sprintf('Value named "%s" is not defined.', $name));
        }
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function __set($name, $value)
    {
        throw new \LogicException(sprintf('You cannot set a value to an instance of %s.', __CLASS__));
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        $value = $this->get($name);
        return isset($value);
    }

    /**
     * @param string|null $name
     * @return mixed|array
     */
    public function get($name = null)
    {
        if (isset($name)) {
            return $this->__get($name);
        } else {
            return get_object_vars($this);
        }
    }
}