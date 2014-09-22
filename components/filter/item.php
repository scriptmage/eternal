<?php

namespace eternal\components;

class Filter_Item
{

    private $_name = '';
    private $_rules = array();

    private function set_message($name, $message)
    {
        $this->_rules['message'][$name] = $message;
    }

    private function add_rule($name, $message = '', $params = array())
    {
        $this->_rules['rules'][$name] = $params;
        $this->set_message($name, $message);
    }

    public function __construct($name)
    {
        $this->_name = $name;
    }

    public function & bool($message = '')
    {
        $this->_rules['filter'] = FILTER_VALIDATE_BOOLEAN;
        $this->set_message('filter', $message);
        return $this;
    }

    public function & int($message = '')
    {
        $this->_rules['filter'] = FILTER_VALIDATE_INT;
        $this->set_message('filter', $message);
        return $this;
    }

    public function & float($message = '')
    {
        $this->_rules['filter'] = FILTER_VALIDATE_FLOAT;
        $this->set_message('filter', $message);
        return $this;
    }

    public function & email($message = '')
    {
        $this->_rules['filter'] = FILTER_VALIDATE_EMAIL;
        $this->set_message('filter', $message);
        return $this;
    }

    public function & ip($message = '')
    {
        $this->_rules['filter'] = FILTER_VALIDATE_IP;
        $this->set_message('filter', $message);
        return $this;
    }

    public function & required($message = '')
    {
        $this->add_rule('required', $message);
        $this->set_message('required', $message);
        return $this;
    }

    public function & url($message = '', $flags = array())
    {
        $this->_rules['filter'] = FILTER_VALIDATE_URL;
        $this->_rules['flags'] = $flags;
        $this->set_message('filter', $message);
        return $this;
    }

    public function & regexp($regexp, $message = '')
    {
        $this->_rules['filter'] = FILTER_VALIDATE_REGEXP;
        $this->_rules['regexp'] = $regexp;
        $this->set_message('filter', $message);
        return $this;
    }

    public function & min($limit, $message = '')
    {
        $this->add_rule('min', $message, (int) $limit);
        return $this;
    }

    public function & max($limit, $message = '')
    {
        $this->add_rule('max', $message, (int) $limit);
        return $this;
    }

    public function get()
    {
        $rules = '';
        foreach ($this->_rules['rules'] as $rule => $param) {
            if (is_array($param)) {
                $rules .= sprintf('%s%s|', $rule, empty($param) ? '' : implode(':', $param));
            } else {
                $rules .= sprintf('%s%s|', $rule, empty($param) ? '' : ':' . $param);
            }
        }
        $this->_rules['rules'] = rtrim($rules, '|');
        return $this->_rules;
    }

    public function get_name()
    {
        return $this->_name;
    }

}