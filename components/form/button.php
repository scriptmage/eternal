<?php

namespace eternal\components\form;

class Button extends \eternal\Base_Form
{

    protected $_validTypes = array('button', 'reset', 'submit');
    protected $_type = '';
    protected $_text = '';

    public function __construct($name, $text, $type = 'submit', $attributes = array())
    {
        $this->_type = $type;
        $this->_text = $text;
        if (!in_array($this->_type, $this->_validTypes)) {
            $this->_type = 'button';
        }
        $attributes = array_merge($attributes, array('name' => $name));

        parent::__construct($attributes, $name);
    }

    public function render()
    {
        return sprintf('<button %s type="%s">%s</button>', $this->atostr(), $this->_type, $this->_text);
    }

}
