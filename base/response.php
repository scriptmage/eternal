<?php

namespace eternal;

class Base_Response extends Base_Object
{

    private $_status = 200;
    private $_data = NULL;

    private function get_headers()
    {
        $headers = array();
        foreach (headers_list() as $header) {
            $header = explode(":", $header);
            $key = strtoupper(array_shift($header));
            $headers[$key] = trim(implode(":", $header));
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

    public function status($code = NULL)
    {
        if (is_null($code)) {
            return $this->_status;
        }

        $this->_status = $code;
        $protocol = 'HTTP/1.0';
        if (isset($this->_app->server->SERVER_PROTOCOL)) {
            $protocol = $this->_app->server->SERVER_PROTOCOL;
        }
        header($protocol . ' ' . $code . ' ' . $this->get_status($code), TRUE, $code);
    }
    
    private function get_status($code) {
    	$codeStatus = array(
    			100 => 'Continue',
    			101 => 'Switching Protocols',
    			200 => 'OK',
    			201 => 'Created',
    			202 => 'Accepted',
    			203 => 'Non-Authoritative Information',
    			204 => 'No Content',
    			205 => 'Reset Content',
    			206 => 'Partial Content',
    			300 => 'Multiple Choices',
    			301 => 'Moved Permanently',
    			302 => 'Found',
    			303 => 'See Other',
    			304 => 'Not Modified',
    			305 => 'Use Proxy',
    			307 => 'Temporary Redirect',
    			400 => 'Bad Request',
    			401 => 'Unauthorized',
    			402 => 'Payment Required',
    			403 => 'Forbidden',
    			404 => 'Not Found',
    			405 => 'Method Not Allowed',
    			406 => 'Not Acceptable',
    			407 => 'Proxy Authentication Required',
    			408 => 'Request Time-out',
    			409 => 'Conflict',
    			410 => 'Gone',
    			411 => 'Length Required',
    			412 => 'Precondition Failed',
    			413 => 'Request Entity Too Large',
    			414 => 'Request-URI Too Large',
    			415 => 'Unsupported Media Type',
    			416 => 'Requested range not satisfiable',
    			417 => 'Expectation Failed',
    			500 => 'Internal Server Error',
    			501 => 'Not Implemented',
    			502 => 'Bad Gateway',
    			503 => 'Service Unavailable',
    			504 => 'Gateway Time-out'
    	);
    	return $codeStatus[$code];
    }

}
