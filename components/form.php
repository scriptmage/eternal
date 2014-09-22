<?php

namespace eternal\components;

class Form extends \eternal\Base_Form
{

    protected $_app = NULL;
    protected $_method = '';
    protected $_fields = array();
    protected $_form = '';
    protected $_filter = NULL;

    public function __construct($app, $name = 'send', $method = 'POST')
    {
        $this->_app = $app;

        $this->_method = (strtoupper($method) == 'GET') ? 'GET' : 'POST';
        if ($this->_method == 'GET') {
            $this->_filter = $this->_app->filter($this->_app->input->get());
        } else {
            $this->_filter = $this->_app->filter($this->_app->input->post());
        }

        $this->add_attr('name', $name);
        $this->add_attr('method', $this->_method);
        parent::__construct($this->_attributes, $name);
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        $this->_form = sprintf(
            '<form %s><a name="%s"></a>%s', $this->atostr(), $this->_name,
            ((isset($_GET[$this->_name]) or isset($_POST[$this->_name])) ? $this->_filter : '')
        );
        foreach ($this->_fields as $field) {
            if ($field instanceof \eternal\Base_Form) {
                $this->_form .= $field->render();
            } else {
                $this->_form .= $field;
            }
        }
        $this->_form .= sprintf('<div style="display: none;"><input type="text" name="%s" value="">', $this->_name);
        if ($this->_app->config->secure->protector->csrf->filter) {
            $this->_form .= sprintf(
                '<input type="hidden" name="%s" value="%s">', $this->_app->config->secure->protector->csrf->name,
                $this->_app->protector->get_token()
            );
        }
        $this->_form .= '</div></form>';
        return $this->_form;
    }

    public function & open($attributes = array())
    {
        if (!empty($attributes)) {
            $this->add_attrs($attributes);
        }

        if (!isset($attributes['action'])) {
            $this->add_attr('action');
        }
        return $this;
    }

    public function & fieldset()
    {
        foreach (func_get_args() as $field) {
            $this->_fields[] = $field;
            if (($field instanceof Input)) {
                if ($field->get_type() == 'file') {
                    $this->add_attr('enctype', 'multipart/form-data');
                }
            }
        }
        return $this;
    }

    public function validate()
    {
        if ($this->_app->request->is($this->_method)) {
            $token = $this->get_data($this->_app->config->secure->protector->csrf->name);
            $trap = $this->get_data($this->_name);
            return $this->_filter->validate() and $this->_app->protector->check($token) and empty($trap);
        }
        return FALSE;
    }

    public function & rule($name)
    {
        return $this->_filter->add($name);
    }

    public function is_valid($name)
    {
        $errors = $this->_filter->errors();
        return !isset($errors[$name]);
    }

    public function get_data($name)
    {
        $data = NULL;
        if (($this->_method == 'GET') and isset($this->_app->input->get->{$name})) {
            $data = $this->_app->input->get->{$name};
        } elseif (($this->_method == 'POST') and isset($this->_app->input->post->{$name})) {
            $data = $this->_app->input->post->{$name};
        }
        return $data;
    }

}
