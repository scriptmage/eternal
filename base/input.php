<?php

namespace eternal;

class Base_Input extends Base_Object
{
	
	const 
		GET = 1,
		POST = 2,
		COOKIE = 4,
		SESSION = 8;

    private function isFilterRunning($xssFilter) 
    {
    	return is_null($xssFilter) ? $this->_app->config->secure->protector->xss_filter : $xssFilter;
    }
    
    private function getDataArray($data_array, $xssFilter) 
    {
    	$result = $data_array;
    	if($this->isFilterRunning($xssFilter)) {
    		$result = $this->_app->protector->sanitize($data_array); 
    	}
    	return $result;
    }
    
    private function cleanName($name) 
    {
    	return preg_replace('~[^a-zA-Z0-9_]~', '', $name);
    }
    
    private function getValue($name, $dataArray, $encrypted = false, $xssFilter = NULL) 
    {
    	$cleanedName = $this->cleanName($name);
    	if (isset($dataArray[$cleanedName])) {
    		$value = $dataArray[$cleanedName];
    		if ($encrypted) {
    			$value = $this->_app->decrypt($value);
    		}
    		return $this->isFilterRunning($xssFilter) ? $this->_app->protector->sanitize($value) : $value;
    	}
    	return NULL;
    }
    
    public function del($name, $type = self::SESSION)
    {
    	$cleanedName = $this->cleanName($name);
        switch (strtolower($type)) {
            case self::GET:
                if (isset($_GET[$cleanedName])) {
                    unset($_GET[$cleanedName]);
                }
                break;
            case self::POST:
                if (isset($_POST[$cleanedName])) {
                    unset($_POST[$cleanedName]);
                }
                break;
            case self::COOKIE:
            	if (isset($_COOKIE[$cleanedName])) {
		            setcookie($cleanedName, FALSE, 1);
		            unset($_COOKIE[$cleanedName]);
            	}
                break;
            case self::SESSION:
                if (isset($_SESSION[$cleanedName])) {
                    unset($_SESSION[$cleanedName]);
                }
        }
    }

    public function get($name = NULL, $xssFilter = NULL)
    {
        if (is_null($name)) {
            return $this->getDataArray($_GET);
        }
        
        return $this->getValue($name, $_GET, false, $xssFilter);
    }

    public function post($name = NULL, $xssFilter = NULL)
    {
        if (is_null($name)) {
            return $this->getDataArray($_POST);
        }
        
        return $this->getValue($name, $_POST, false, $xssFilter);
    }

    public function session($name = NULL, $value = NULL, $xssFilter = NULL)
    {
        if (is_null($name)) {
            return $this->getDataArray($_SESSION);
        }
        if (is_null($value)) {
        	return $this->getValue($name, $_SESSION, $this->_app->config->secure->encrypt->session, $xssFilter);
        }
        if ($this->_app->config->secure->encrypt->session) {
            $value = $this->_app->encrypt($value);
        }
        
        $cleanedName = $this->cleanName($name);
        $_SESSION[$cleanedName] = $value;
    }

    public function cookie($name = NULL, $value = NULL, $options = array(), $xssFilter = NULL)
    {
        if (is_null($name)) {
            return $this->getDataArray($_COOKIE);
        }
        
        if (is_null($value)) {
        	return $this->getValue($name, $_COOKIE, $this->_app->config->secure->encrypt->cookie, $xssFilter);
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

        if ($this->_app->config->secure->encrypt->cookie) {
            $value = $this->_app->encrypt($value);
        }

        $cleanedName = $this->cleanName($name);
        return setcookie(
            $cleanedName, $value, $options['expire'], $options['path'], $options['domain'], $options['secure'],
            $options['httponly']
        );
    }

}