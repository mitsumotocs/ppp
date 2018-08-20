<?php
namespace PPP\View;

class View implements ViewInterface
{
    const HTTP_CONTENT_TYPE = 'text/plain';

    public $data;

    /**
     * View constructor.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return print_r($this->data, true);
    }

    /**
     * @return void
     */
    public function render()
    {
        header('Content-Type: ' . static::HTTP_CONTENT_TYPE);
        echo $this->__toString();
    }
}