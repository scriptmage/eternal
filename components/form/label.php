<?php

namespace eternal\components\form;

class Label extends Message
{

    public function __construct($text, $for = '', $attributes = array())
    {
        parent::__construct($text, $attributes);
        if (!empty($for)) {
            $this->add_attr('for', $for);
        }
    }

    public function render()
    {
        return sprintf('<label %s>%s</label>', $this->atostr(), $this->_text);
    }

}
