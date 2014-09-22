<?php

namespace eternal\components\form;

class Select extends \eternal\Base_Form
{

    protected $_value = NULL;
    protected $_values = array();

    public function __construct($name, $value = '', $values = array(), $attributes = array())
    {
        $this->_value = $value;
        $this->_values = $values;
        $attributes = array_merge($attributes, array('name' => $name));

        if (!isset($attributes['id'])) {
            $attributes = array_merge($attributes, array('id' => $name));
        }

        parent::__construct($attributes, $name);
    }

    public function render()
    {
        $options = '';
        foreach ($this->_values as $value => $text) {
            $selected = '';
            if ($this->_value == $value) {
                $selected = ' selected="selected"';
            }
            $options .= sprintf('<option value="%s"%s>%s</option>', $value, $selected, $text);
        }
        return sprintf('<select %s>%s</select>', $this->atostr(), $options);
    }

}
