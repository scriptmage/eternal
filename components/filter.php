<?php

namespace eternal\components;

class Filter
{

    private $_items = array();
    private $_errors = array();
    private $_data = array();
    private $_template = '<div class="alert alert-danger user-error" role="alert"><ul>%s</ul></div>';
    private $_prefix = '<li>';
    private $_postfix = '</li>';

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function prefix($str)
    {
        $this->_prefix = $str;
    }

    public function postfix($str)
    {
        $this->_postfix = $str;
    }

    public function template($string = NULL)
    {
        if (is_null($string)) {
            return $this->_template;
        }
        $this->_template = $string;
    }

    public function & add($name)
    {
        $this->_items[$name] = new Filter_Item($name);
        return $this->_items[$name];
    }

    public function validate($rules = NULL)
    {

        if (is_null($rules)) {
            foreach ($this->_items as $item) {
                $rules[$item->get_name()] = $item->get();
            }
        }
        
        if(empty($rules)) {
            return FALSE;
        }

        foreach ($rules as $name => $rule) {
            $required = array_search('required', explode('|', $rule['rules']));
            switch ($rule['filter']) {
                case FILTER_VALIDATE_BOOLEAN:
                    if ($required or $this->required($name)) {
                        if ($this->check(
                                $name, FILTER_VALIDATE_BOOLEAN, array('flags' => FILTER_NULL_ON_FAILURE)
                            ) === NULL) {
                            $this->_errors[$name]['filter'] = $rule['message']['filter'];
                        }
                    }
                    break;
                case FILTER_VALIDATE_EMAIL:
                    if ($required or $this->required($name)) {
                        if ($this->check($name, FILTER_VALIDATE_EMAIL) === FALSE) {
                            $this->_errors[$name]['filter'] = $rule['message']['filter'];
                        }
                    }
                    break;
                case FILTER_VALIDATE_FLOAT:
                    if ($required or $this->required($name)) {
                        if ($this->check($name, FILTER_VALIDATE_FLOAT) === FALSE) {
                            $this->_errors[$name]['filter'] = $rule['message']['filter'];
                        }
                    }
                    break;
                case FILTER_VALIDATE_INT:
                    if ($required or $this->required($name)) {
                        if ($this->check($name, FILTER_VALIDATE_INT) === FALSE) {
                            $this->_errors[$name]['filter'] = $rule['message']['filter'];
                        }
                    }
                    break;
                case FILTER_VALIDATE_IP:
                    if ($required or $this->required($name)) {
                        if ($this->check($name, FILTER_VALIDATE_IP) === FALSE) {
                            $this->_errors[$name]['filter'] = $rule['message']['filter'];
                        }
                    }
                    break;
                case FILTER_VALIDATE_REGEXP:
                    if ($required or $this->required($name)) {
                        if ($this->check($name, FILTER_VALIDATE_REGEXP,
                                array("options" => array("regexp" => $rule['regexp']))
                            ) === FALSE) {
                            $this->_errors[$name]['filter'] = $rule['message']['filter'];
                        }
                    }
                    break;
                case FILTER_VALIDATE_URL:
                    if ($required or $this->required($name)) {
                        if ($this->check(
                                $name, FILTER_VALIDATE_URL, isset($rule['flags']) ? [] : $rule['flags']
                            ) === FALSE) {
                            $this->_errors[$name]['filter'] = $rule['message']['filter'];
                        }
                    }
                    break;
                default:
                    $this->_errors[$name]['filter'] = sprintf('Invalid filter type: %d', $rule['filter']);
            }
            $this->cmd($name, $rule);
        }
        return empty($this->_errors);
    }

    public function valid($field)
    {
        return isset($this->_errors[$field]) === FALSE;
    }

    private function cmd($field, $rule)
    {
        foreach (array_filter(explode('|', $rule['rules'])) as $params) {
            $params = explode(':', $params);
            $action = array_shift($params);
            if (is_callable(array($this, $action))) {
                if (!call_user_func_array(
                        array($this, $action), array($field, $params, isset($rule['flags']) ? $rule['flags'] : array())
                    )) {
                    if (!isset($this->_errors[$field]['required'])) {
                        $this->_errors[$field][$action] = $rule['message'][$action];
                        foreach ($params as $index => $param) {
                            $this->_errors[$field][$action] = str_replace(
                                '{{p' . ($index + 1) . '}}', $param, $this->_errors[$field][$action]
                            );
                        }
                    }
                }
            }
        }
    }

    public function __toString()
    {
        $str = '';
        foreach ($this->_errors as $errors) {
            foreach ($errors as $error) {
                if (!empty($error)) {
                    $str .= sprintf('%s%s%s', $this->_prefix, $error, $this->_postfix);
                }
            }
        }
        return empty($str) ? '' : sprintf($this->_template, $str);
    }

    public function errors()
    {
        return $this->_errors;
    }

    private function required($field)
    {
        if (is_array($this->_data[$field])) {
            return !empty($this->_data[$field]);
        }
        return trim($this->_data[$field]) != '';
    }

    private function min($field, $params, $flags)
    {
        return $this->check(
                $field, FILTER_VALIDATE_INT, array('options' => array('min_range' => $params[0]), 'flags' => $flags)
        );
    }

    private function max($field, $params, $flags)
    {
        return $this->check(
                $field, FILTER_VALIDATE_INT, array('options' => array('max_range' => $params[0]), 'flags' => $flags)
        );
    }

    private function check($field, $filter = FILTER_DEFAULT, $options = array())
    {
        if (is_array($this->_data[$field])) {
            $ret = filter_var_array($this->_data[$field], $filter, $options);
        } else {
            $ret = filter_var($this->_data[$field], $filter, $options);
        }
        return $ret;
    }

}
