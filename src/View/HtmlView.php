<?php
namespace PPP\View;

class HtmlView extends View implements ViewInterface
{
    const HTTP_CONTENT_TYPE = 'text/html';

    protected $template;

    /**
     * HtmlView constructor.
     * @param string|null $template
     */
    public function __construct($template = null)
    {
        parent::__construct();
        if (isset($template)) {
            $this->setTemplate($template);
        }
    }

    /**
     * @param $file
     * @return $this
     * @throws \LogicException
     */
    public function setTemplate($file)
    {
        if (is_readable($file)) {
            $this->template = $file;
            return $this;
        } else {
            throw new \LogicException(sprintf('Template "%s" is not available.', $file), 500);
        }
    }

    /**
     * @return void
     */
    public function render()
    {
        header('Content-Type: ' . static::HTTP_CONTENT_TYPE);
        include_once $this->template;
    }
}