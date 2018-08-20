<?php
namespace PPP\Controller;

class Controller
{
    /**
     * @param $name
     * @param $arguments
     * @throws \LogicException
     */
    public function __call($name, $arguments)
    {
        throw new \LogicException(sprintf('Action "%s" is not implemented in %s.', $name, get_class($this)), 404);
    }
}