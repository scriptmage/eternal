<?php

namespace eternal;

class Base_Template extends Base_Object
{

    private $_vars = array();
    private $_alwaysLoaded = array();
    public $functions = NULL;
    public $header = NULL;

    public function __call($name, $arguments)
    {
        $pathFunctions = $this->_app->config->folders->include . '/eternal/functions';
        if (isset($this->functions->{$name})) {
            return call_user_func_array($this->functions->{$name}, $arguments);
        } elseif (file_exists($file = sprintf('%s/view/%s.php', $pathFunctions, $name))) {
            $this->functions->{$name} = include_once($file);
            return call_user_func_array($this->functions->{$name}, $arguments);
        } elseif (file_exists($file = sprintf('%s/%s.php', $pathFunctions, $name))) {
            $this->functions->{$name} = include_once($file);
            return call_user_func_array($this->functions->{$name}, $arguments);
        } elseif ($this->_app->debug) {
            throw new \Exception(sprintf('Call to undefined function %s()', htmlspecialchars($name)));
        }
    }

    public function __construct(\eternal\Framework &$app)
    {
        parent::__construct($app);
        $this->header = new Base_Template_Header;
        $this->functions = new \stdClass;
    }

    public function display($template, $data = array())
    {
        $this->_vars = array_merge($this->_vars, $data);
        echo $this->render($template);
    }

    public function fetch($template, $data = array())
    {
        $this->_vars = array_merge($this->_vars, $data);
        return $this->render($template);
    }

    public function & always($name, $value)
    {
        $this->_alwaysLoaded[$name] = $value;
        return $this;
    }

    public function & assign($name, $value)
    {
        $this->_vars[$name] = $value;
        return $this;
    }

    public function get_vars()
    {
        return $this->_vars;
    }

    public function get_var($name)
    {
        if (isset($this->_vars[$name])) {
            return $this->_vars[$name];
        }
        return NULL;
    }

}
