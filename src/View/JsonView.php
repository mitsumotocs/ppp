<?php
namespace PPP\View;

class JsonView extends View implements ViewInterface
{
    const HTTP_CONTENT_TYPE = 'application/json';

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->data);
    }
}