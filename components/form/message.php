<?php

namespace eternal\components\form;

class Message extends \eternal\Base_Form
{

    protected
        $_text = '';

    public function __construct($text, $attributes = array())
    {
        parent::__construct($attributes);
        $this->_text = $text;
    }

    public function render()
    {
        return sprintf('<span %s>%s</span>', $this->atostr(), $this->_text);
    }

}
