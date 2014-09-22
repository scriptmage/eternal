<?php

namespace eternal;

class Base_Flash extends Base_Object
{

    public function get($name, $xssFilter = NULL)
    {
        $data = $this->_app->input->session('flash_data_' . $name, NULL, $xssFilter);
        $this->_app->input->del('flash_data_' . $name);
        return $data;
    }

    public function set($name, $value, $xssFilter = NULL)
    {
        $name = preg_replace('~[^a-zA-Z0-9_]~', '', $name);
        $this->_app->input->session('flash_data_' . $name, $value, $xssFilter);
    }

    public function keep($name, $xssFilter = NULL)
    {
        return $this->_app->input->session('flash_data_' . $name, NULL, $xssFilter);
    }

}
