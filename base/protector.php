<?php

namespace eternal;

class Base_Protector extends Base_Object
{

    private function new_token()
    {
        $this->_app->input->session($this->_app->config->secure->protector->csrf->session, sha1(uniqid(time(), TRUE)));
    }

    public function check($token)
    {
        $valid = ($this->_app->input->session($this->_app->config->secure->protector->csrf->session) == $token);
        $this->new_token();
        return $valid;
    }

    public function get_token()
    {
        if (!$this->_app->input->session($this->_app->config->secure->protector->csrf->session)) {
            $this->new_token();
        }
        return $this->_app->input->session($this->_app->config->secure->protector->csrf->session);
    }

    public function sanitize($value)
    {
        if (is_array($value)) {
            $value = filter_var_array($value, FILTER_SANITIZE_STRING);
        } else {
            $value = filter_var($value, FILTER_SANITIZE_STRING);
        }
        return $value;
    }

}
