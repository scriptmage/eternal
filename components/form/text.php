<?php

namespace eternal\components\form;

class Text extends \eternal\Base_Form
{

    protected $_text = '';

    public function __construct($name, $text, $attributes = array())
    {
        $this->_text = $text;
        $attributes = array_merge($attributes, array('name' => $name));

        if (!isset($attributes['id'])) {
            $attributes = array_merge($attributes, array('id' => $name));
        }

        parent::__construct($attributes, $name);
    }

    public function render()
    {
        return sprintf('<textarea %s>%s</textarea>', $this->atostr(), htmlspecialchars($this->_text));
    }

}
