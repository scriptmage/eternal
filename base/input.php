<?php

namespace eternal;

class Base_Input extends Base_Object
{

    private function is_encrypted_data($name)
    {
        switch ($name) {
            case 'session':
                return $this->_app->config->secure->encrypt->session;
                break;
            case 'cookie':
                return $this->_app->config->secure->encrypt->cookie;
                break;
            default:
                return NULL;
        }
    }

    public function del($name, $type = 'session')
    {
        switch (strtolower($type)) {
            case 'get':
                if (isset($_GET[$name])) {
                    unset($_GET[$name]);
                }
                break;
            case 'post':
                if (isset($_POST[$name])) {
                    unset($_POST[$name]);
                }
                break;
            case 'cookie':
                $this->_app->input->cookie($name, NULL, array('expire' => -1));
                break;
            case 'session':
                if (isset($_SESSION[$name])) {
                    unset($_SESSION[$name]);
                }
        }
    }

    public function get($name = NULL, $xssFilter = NULL)
    {
        $runFilter = is_null($xssFilter) ? $this->_app->config->secure->protector->xss_filter : $xssFilter;
        if (is_null($name)) {
            return $runFilter ? $this->_app->protector->sanitize($_GET) : $_GET;
        }
        $value = isset($_GET[$name]) ? $_GET[$name] : NULL;
        if ($runFilter and ! is_null($value)) {
            $value = $this->_app->protector->sanitize($value);
        }
        return $value;
    }

    public function post($name = NULL, $xssFilter = NULL)
    {
        $runFilter = is_null($xssFilter) ? $this->_app->config->secure->protector->xss_filter : $xssFilter;
        if (is_null($name)) {
            return $runFilter ? $this->_app->protector->sanitize($_POST) : $_POST;
        }
        $value = isset($_POST[$name]) ? $_POST[$name] : NULL;
        if ($runFilter and ! is_null($value)) {
            $value = $this->_app->protector->sanitize($value);
        }
        return $value;
    }

    public function session($name = NULL, $value = NULL, $xssFilter = NULL)
    {
        $name = preg_replace('~[^a-zA-Z0-9_]~', '', $name);
        $runFilter = is_null($xssFilter) ? $this->_app->config->secure->protector->xss_filter : $xssFilter;
        if (is_null($name)) {
            return $runFilter ? $this->_app->protector->sanitize($_SESSION) : $_SESSION;
        }
        if (is_null($value)) {
            if (isset($_SESSION[$name])) {
                $value = $_SESSION[$name];
                if ($this->is_encrypted_data('session')) {
                    $value = $this->_app->decrypt($_SESSION[$name]);
                }
                return $runFilter ? $this->_app->protector->sanitize($value) : $value;
            }
            return NULL;
        }
        if ($this->_app->config->secure->encrypt->session) {
            $value = $this->_app->encrypt($value);
        }
        $_SESSION[$name] = $value;
    }

    public function cookie($name = NULL, $value = NULL, $options = array(), $xssFilter = NULL)
    {
        $name = preg_replace('~[^a-zA-Z0-9_]~', '', $name);
        $runFilter = is_null($xssFilter) ? $this->_app->config->secure->protector->xss_filter : $xssFilter;
        if (is_null($name)) {
            return $runFilter ? $this->_app->protector->sanitize($_COOKIE) : $_COOKIE;
        }
        if (is_null($value)) {
            if (isset($_COOKIE[$name])) {
                $value = $_COOKIE[$name];
                if ($this->is_encrypted_data('cookie')) {
                    $value = $this->_app->decrypt($value);
                }
                return $runFilter ? $this->_app->protector->sanitize($value) : $value;
            }
            return NULL;
        }

        $options = array_merge(
            array(
            'expire' => time() + 60 * 60 * 24 * 7,
            'path' => '/',
            'domain' => $this->_app->server->SERVER_NAME,
            'secure' => FALSE,
            'httponly' => TRUE
            ), $options
        );

        if ($options ['expire'] === -1) {
            setcookie($name, FALSE, 1);
            unset($_COOKIE[$name]);
            return TRUE;
        }

        if ($this->_app->config->secure->encrypt->cookie) {
            $value = $this->_app->encrypt($value);
        }

        return setcookie(
            $name, $value, $options['expire'], $options['path'], $options['domain'], $options['secure'],
            $options['httponly']
        );
    }

}
