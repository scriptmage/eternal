<?php

namespace eternal;

abstract class Base_Form
{

    protected $_name = '';
    protected $_attributes = array();

    protected function atostr($attrs = array())
    {
        if (empty($attrs)) {
            $attrs = $this->_attributes;
        }
        return implode(
            ' ', 
            array_map(
                function ($v, $k) {
                    return sprintf('%s="%s"', $k, $v);
                }, $attrs, array_keys($attrs)
            )
        );
    }

    public function __construct($attributes = array(), $name = '')
    {
        $this->_attributes = $attributes;
        $this->_name = $name;
    }

    public function add_attr($name, $value = '')
    {
        $this->_attributes[$name] = $value;
    }

    public function add_attrs($attrs)
    {
        if (is_array($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->_attributes[$name] = $value;
            }
        }
    }

    public function get_name()
    {
        return $this->_name;
    }

    abstract public function render();
}
