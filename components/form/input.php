<?php

namespace eternal\components\form;

class Input extends \eternal\Base_Form
{

    protected $_input = '';
    protected $_elements = array();
    protected $_value = NULL;
    protected $_validTnputs = array('button', 'checkbox', 'color', 'date', 'datetime', 'datetime-local', 'email', 
        'file', 'hidden', 'image', 'month', 'number', 'password', 'radio', 'range', 'reset', 'search', 'submit', 
        'tel', 'text', 'time', 'url', 'week');

    public function __construct()
    {
        $args = func_get_args();
        $this->_input = array_shift($args);

        if (!in_array($this->_input, $this->_validTnputs)) {
            $this->_input = 'text';
        }

        switch ($this->_input) {
            case 'checkbox':
            case 'radio':
                list($name, $this->_value, $this->_elements, $attributes) = $args;

                if (!isset($attributes['prefix']) or ! isset($attributes['postfix'])) {
                    $attributes['prefix'] = '<label>';
                    $attributes['postfix'] = '</label>';
                }
                break;
            default:
                list($name, $this->_value, $attributes) = $args;
        }

        if (isset($attributes) and is_array($attributes)) {
            $attributes = array_merge($attributes, array('name' => $name));
        } else {
            $attributes = array('name' => $name);
        }

        if (!isset($attributes['id'])) {
            $attributes = array_merge($attributes, array('id' => $name));
        }

        parent::__construct($attributes, $name);
    }

    public function render()
    {
        switch ($this->_input) {
            case 'checkbox':
            case 'radio':
                $str = '';
                foreach ($this->_elements as $value => $text) {
                    $checked = '';
                    if((is_array($this->_value) and in_array($value, $this->_value)) or ($this->_value == $value)) {
                        $checked = ' checked="checked"'; 
                    }
                    $str .= sprintf('%s<input %s type="%s" value="%s"%s>%s%s', $this->_attributes['prefix'],
                        $this->atostr(
                            array_diff_assoc(
                                $this->_attributes,
                                array(
                                    'postfix' => $this->_attributes['postfix'], 
                                    'prefix' => $this->_attributes['prefix']
                                )
                            )
                        ),
                        $this->_input, htmlspecialchars($value),
                        $checked,
                        htmlspecialchars($text), 
                        $this->_attributes['postfix']
                    );
                }
                return $str;
                break;
            default:
                return sprintf('<input %s type="%s" value="%s">', $this->atostr(), $this->_input,
                    htmlspecialchars($this->_value)
                );
        }
    }

    public function get_type()
    {
        
        return $this->_input;
    }

}
