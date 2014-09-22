<?php

namespace eternal;

class Base_Request extends Base_Object
{

    private $_data = NULL;

    private function get_headers()
    {
        $headers = array();
        foreach ($this->_app->server as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = strtoupper(substr($key, 5));
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    public function header($name = NULL)
    {
        if (is_null($this->_data)) {
            $this->_data = new \ArrayObject($this->get_headers(), \ArrayObject::STD_PROP_LIST);
        }
        if (is_null($name)) {
            return $this->_data->getArrayCopy();
        }
        return $this->_data->offsetGet(strtoupper($name));
    }

    public function set($name, $content = '')
    {
        header(sprintf('%s: %s', $name, $content));
    }

    public function is($method)
    {
        $method = strtoupper($method);
        if ($method === "AJAX") {
            return (strtolower($this->_app->server->HTTP_X_REQUESTED_WITH) === 'xmlhttprequest');
        }
        return ($this->_app->server->REQUEST_METHOD === $method);
    }

}
