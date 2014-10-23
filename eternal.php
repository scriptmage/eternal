<?php

namespace eternal;

include_once('autoload.php');
\AutoLoader::addPath(dirname(__FILE__) . '/../');

/**
 * Eternal Micro Framework
 *
 * @version 0.1
 * @link https://github.com/scriptmage/eternal
 * 
 * $this->config    Eternal configuration
 *  ->base            General section
 *    ->root            Root directory
 *    ->url             Site URL
 *    ->charset          Az oldal karakter kódolása [UTF-8]
 *  ->database      Database section
 *    ->host          Hostname [localhost]
 *    ->username      Username [root]
 *    ->password      Password
 *    ->name          Database name
 *    ->provider      Provider [mysql]
 *    ->port          Port number [3306]
 *    ->charset        A kapcsolat karakter kódolása [UTF8]
 *  ->session       Session section
 *    ->name          Get and/or set the current session name
 *    ->lifetime      Lifetime of the session cookie, defined in seconds. 
 *    ->path          Path on the domain where the cookie will work. [/]
 *    ->domain        Cookie domain. [$_SERVER['SERVER_NAME']]
 *    ->secure        If TRUE cookie will only be sent over secure connections. 
 *    ->httponly      If set to TRUE then PHP will attempt to send the httponly flag when setting 
 *                    the session cookie. [TRUE]
 *  ->secure        Secure section
 *    ->encrypt       Have to use cookie encryption
 *      ->key           Encryption key [UnwiT!TRokE42lorAn;rutY65mUN%aha96dopa]
 *      ->cookie        Encrypt for the cookies [TRUE]
 *      ->session       Encrypt for the session cookie [TRUE]
 *   ->protector      Global object for protected user inputs
 *      ->xss_filter    Use global XSS protector [TRUE]
 *      ->csrf        CSRF Protector settings
 *          ->filter    Use CSRF Protector [TRUE]
 *          ->name      Name of hidden input field [token]
 *          ->session   Session name's what Protector will be use [eternal_csrf_protector_session]
 *  ->template      Template section
 *    ->compress      Have to compress the output [TRUE]
 *    ->extension     Template file's extension [php]
 *  ->folders       Folders section
 *    ->cache         Folder for cache
 *    ->log           Folder for log
 *    ->template      Folder for template
 *    ->include       Folder for include
 *  ->namespaces    Namespaces section
 *    ->controller    Controllers' namespace
 */
class Framework implements Base_Log_IAware
{

    const VERSION = '0.1';

    static private $_once = NULL;
    private $_logger = NULL;
    private $_hooks = array();
    private $_controllers = array();
    private $_group = array();
    private $_routes = array();
    private $_root = '';
    private $_protocol = 'http';
    private $_routingMasks = array();
    private $_validHookNames = array(
        'session.before',
        'session.after',
        'route.before',
        'route.after',
        'routing.before',
        'routing.after'
    );
    public $view = NULL;
    public $flash = NULL;
    public $input = NULL;
    public $request = NULL;
    public $response = NULL;
    public $functions = NULL;
    public $protector = NULL;
    public $debug = FALSE;
    public $server = array();
    public $const = array();
    public $errors = array();
    public $config = array();

    private function __construct()
    {
        $this->_root = dirname(__FILE__) . '/';
        $constants = get_defined_constants(true);
        unset($_REQUEST);
            
        $this->_arrayToObj(
            array(
                'server' => array_map(
                    function($param) {
                        $param = filter_var($param, FILTER_SANITIZE_STRING);
                        $param = preg_replace(
                            '~(?:(?:ht|f)tps?://)|(?:(?:' . DIRECTORY_SEPARATOR 
                            . ')?\.\.(?:' . DIRECTORY_SEPARATOR . ')?)~',
                            '{filter}', 
                            $param
                        );
                        $param = preg_replace('~[\r\n\t]+~', '', $param);
                        return $param;
                    }, $_SERVER
                ),
                'const' => isset($constants['user']) ? $constants['user'] : '',
                'errors' => array(
                    '_404' => function() {
                        return '<h1>Ooops</h1><p>Page not found!</p>';
                    },
                    '_500' => function() {
                        return '<h1>Ooops</h1><p>Internal server error!</p>';
                    },
                ),
            ), $this
        );

        if(isset($this->server->HTTPS) and ( $this->server->HTTPS == 'on' or $this->server->HTTPS == 1) 
            or isset($this->server->HTTP_X_FORWARDED_PROTO) and ($this->server->HTTP_X_FORWARDED_PROTO == 'https')) {
            $this->_protocol = 'https';
        }
        
        $this->_arrayToObj(
            array(
                'config' => array(
                    'base' => array(
                        'root' => sprintf('%s/', __ETERNAL_ROOT_DIR__),
                        'url' => sprintf('%s://%s/', $this->_protocol, $this->server->HTTP_HOST),
                        'charset' => 'UTF-8'
                    ),
                    'database' => array(
                        'host' => 'localhost',
                        'username' => 'root',
                        'password' => '',
                        'name' => '',
                        'provider' => 'mysql',
                        'port' => 3306,
                        'charset' => 'UTF8',
                    ),
                    'session' => array_merge(
                        array('name' => session_name()),
                        array_merge(
                            session_get_cookie_params(), 
                            array('domain' => $this->server->SERVER_NAME, 'httponly' => TRUE)
                        )
                    ),
                    'secure' => array(
                        'encrypt' => array(
                            'key' => 'UnwiT!TRokE42lorAn;rutY65mUN%aha96dopa',
                            'cookie' => TRUE,
                            'session' => TRUE,
                        ),
                        'protector' => array(
                            'xss_filter' => TRUE,
                            'csrf' => array(
                                'filter' => TRUE,
                                'session' => 'eternal_csrf_protector_session',
                                'name' => 'token'
                            )
                        ),
                    ),
                    'template' => array(
                        'compress' => TRUE,
                        'extension' => 'php',
                    ),
                    'folders' => array(
                        'cache' => sprintf('%s/inc/eternal/cache/', __ETERNAL_ROOT_DIR__),
                        'log' => sprintf('%s/inc/eternal/logs/', __ETERNAL_ROOT_DIR__),
                        'template' => sprintf('%s/inc/views/', __ETERNAL_ROOT_DIR__),
                        'include' => sprintf('%s/inc/', __ETERNAL_ROOT_DIR__)
                    ),
                    'namespaces' => array(
                        'controller' => '\\controllers\\'
                    ),
                ),
            ), $this
        );

        $this->protector = new Base_Protector($this);
        $this->response = new Base_Response($this);
        $this->request = new Base_Request($this);
        $this->input = new Base_Input($this);
        $this->flash = new Base_Flash($this);
        $this->view = new Base_Template($this);
        $this->functions = new \stdClass;

        $this->setLogger(new Base_Logger($this));
        set_error_handler(array($this, 'error_handler'));
        set_exception_handler(array($this, 'exception_handler'));
        register_shutdown_function(array($this, 'shutdown_handler'));
    }

    private function _runRoutes()
    {
        $path = rtrim(parse_url($this->server->REQUEST_URI, PHP_URL_PATH), '/');
        $uriParts = array_filter(explode('/', $path));
        $controller = array_shift($uriParts);
        if (in_array($controller, $this->_controllers)) {
            $method = array_shift($uriParts);
            if (empty($method)) {
                $method = 'index';
            }
            $class = $this->config->namespaces->controller . $controller;
            if (is_callable(array($class, $method))) {
                @call_user_func_array(array(new $class($this), $method), $uriParts);
                return TRUE;
            }
        } else {
            foreach ($this->_routes as $route) {
                if (count($this->_routingMasks)) {
                    foreach ($this->_routingMasks as $mask => $regex) {
                        $route['url'] = str_replace(sprintf('<%s>', $mask), $regex, $route['url']);
                    }
                }
                
                if (preg_match(sprintf('~^%s$~', $route['method']), $this->server->REQUEST_METHOD)) {
                    if (preg_match(sprintf('~^%s$~', rtrim($route['url'], '/')), $path, $matches)) {
                        unset($matches[0]);
                        if (is_callable($route['callback'])) {
                            $this->_runMiddlewares($route['middlewares']);
                            @call_user_func_array($route['callback'], $matches);
                            return TRUE;
                        }
                    }
                }
            }
        }
        return FALSE;
    }

    /**
     * Asszociatív tömböt alakít át stdClass objektummá
     * @param array $array
     * @param stdClass $obj
     * @return stdClass
     */
    private function & _arrayToObj($array, &$obj)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $obj->{$key} = new \stdClass;
                $this->_arrayToObj($value, $obj->{$key});
            } else {
                $obj->{$key} = $value;
            }
        }
        return $obj;
    }

    /**
     * Add a route 
     * @param string $method
     * @param string $path
     * @param callable $callback
     * @param array $middlewares
     */
    private function _addRoute($method, $path, $callback, $middlewares = array())
    {
        array_push(
            $this->_routes,
            array(
                'method' => $method,
                'url' => sprintf('%s%s', implode('/', $this->_group), $path),
                'callback' => $callback,
                'middlewares' => $middlewares
            )
        );
    }

    /**
     * Run all middlewares
     * @param array $middlewares
     */
    private function _runMiddlewares($middlewares)
    {
        if (is_array($middlewares)) {
            foreach ($middlewares as $middleware) {
                if (is_callable($middleware)) {
                    call_user_func($middleware, $this);
                }
            }
        }
    }

    /**
     * Compile added route
     * @param string $method
     * @param array $args
     * @throws Exception
     */
    private function _setRoute($method, $args)
    {
        $middlewares = array();
        $argsNumber = count($args);

        if ($argsNumber < 2) {
            throw new Exception('Invalid argument list', 500);
        } elseif ($argsNumber == 2) {
            list($path, $callback) = $args;
        } else {
            $path = array_shift($args);
            $callback = array_pop($args);
            $middlewares = $args;
        }
        $this->_addRoute($method, $path, $callback, $middlewares);
    }

    static public function & getInstance()
    {
        if (is_null(self::$_once)) {
            self::$_once = new Framework;
        }
        return self::$_once;
    }

    /**
     * Load unknow functions
     * @param type $name
     * @param type $arguments
     * @return type
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if (isset($this->functions->{$name})) {
            return call_user_func_array($this->functions->{$name}, $arguments);
        } elseif (file_exists($file = sprintf('%s/functions/core/%s.php', dirname(__FILE__), $name))) {
            $this->functions->{$name} = include_once($file);
            return call_user_func_array($this->functions->{$name}, $arguments);
        } elseif (file_exists($file = sprintf('%s/functions/%s.php', dirname(__FILE__), $name))) {
            $this->functions->{$name} = include_once($file);
            return call_user_func_array($this->functions->{$name}, $arguments);
        } elseif ($this->debug) {
            throw new \Exception(sprintf('Call to undefined function %s()', htmlspecialchars($name)));
        }
    }

    /**
     * Add a new GET route
     * Az első és az utolsó paraméter között megadható tetszőleges számú middleware callback
     * <code>
     *  $this->get('/', 
     *    function() {
     *      echo 'Middleware 1';
     *    }, 
     *    function() {
     *      echo 'Middleware 2';
     *    }, 
     *    function() {
     *      echo 'Middleware X';
     *    }, 
     *    function() {
     *      echo 'Hello world';
     *    }
     *  );
     * </code>
     * @return \Eternal_Framework
     */
    public function & get()
    {
        $this->_setRoute('GET', func_get_args());
        return $this;
    }

    /**
     * Add a new POST route
     * Az első és az utolsó paraméter között megadható tetszőleges számú middleware callback
     * <code>
     *  $this->post('/', 
     *    function() {
     *      echo 'Middleware 1';
     *    }, 
     *    function() {
     *      echo 'Middleware 2';
     *    }, 
     *    function() {
     *      echo 'Middleware X';
     *    }, 
     *    function() {
     *      echo 'Hello world';
     *    }
     *  );
     * </code>
     * @return \Eternal_Framework
     */
    public function & post()
    {
        $this->_setRoute('POST', func_get_args());
        return $this;
    }

    /**
     * Add a controller object into route
     * @param string $name Controller's class name
     * @return \Eternal_Framework
     */
    public function & controller($name)
    {
        $this->_controllers[] = $name;
        return $this;
    }

    /**
     * Add a new custom route
     * <code>
     *  $this->request('POST|GET|PUT', '/', 
     *    function() {
     *      echo 'Middleware 1';
     *    }, 
     *    function() {
     *      echo 'Middleware 2';
     *    }, 
     *    function() {
     *      echo 'Middleware X';
     *    }, 
     *    function() {
     *      echo 'Hello world';
     *    }
     *  );
     * </code>
     * @return \Eternal_Framework
     */
    public function & request()
    {
        $args = func_get_args();
        $method = strtoupper(array_shift($args));
        $this->_setRoute($method, $args);
        return $this;
    }

    /**
     * Az Eternal_Framework::get, Eternal_Framework::post és Eternal_Framework::request parancsokat 
     * tudod csoportokba szervezni, melyel a route megadás egyszerűsíthető
     * @param string $path
     * @param callable $callback
     * @return \Eternal_Framework
     */
    public function & group($path, $callback)
    {
        array_push($this->_group, rtrim($path, '/'));
        call_user_func($callback, $this);
        array_pop($this->_group);
        return $this;
    }

    /**
     * Route-ok futtatása
     * @global \Eternal_Framework $app
     */
    public function run()
    {
        global $app;
        ob_start();
        error_reporting((int) $this->debug);
        ini_set('display_errors', (bool) $this->debug);
        ini_set('display_startup_errors', (bool) $this->debug);
        ini_set('log_errors', 1);
        @include_once($this->config->folders->include . 'constants.php');
        @include_once($this->config->folders->include . 'config.php');
        session_set_cookie_params($this->config->session->lifetime, $this->config->session->path,
            $this->config->session->domain, $this->config->session->secure, $this->config->session->httponly);
        session_name($this->config->session->name);
        call_user_func($this->_hooks['session.before'], $this);
        session_start();
        call_user_func($this->_hooks['session.after'], $this);
        call_user_func($this->_hooks['route.before'], $this);
        @include_once($this->config->folders->include . 'routes.php');
        call_user_func($this->_hooks['route.after'], $this);
        call_user_func($this->_hooks['routing.before'], $this);
        if ($this->_runRoutes() === FALSE) {
            echo $this->error(404);
        }
        call_user_func($this->_hooks['routing.after'], $this);
    }

    /**
     * 
     * @param integer $code
     * @return string
     */
    public function error($code)
    {
        if (php_sapi_name() != 'cli') {
            $this->response->status($code);
        }
        if (is_callable($this->errors->{'_' . $code})) {
            return call_user_func($this->errors->{'_' . $code}, $this);
        }
    }

    /**
     * 
     * @param type $name
     * @param type $callback
     * @return \Eternal_Framework
     */
    public function & hook($name, $callback)
    {
        if (in_array($name, $this->_validHookNames)) {
            $this->_hooks[$name] = $callback;
        }
        return $this;
    }
    
    public function getProtocol() 
    {
        return $this->_protocol;
    }
    
    public function setLogger(Base_Log_Interface $logger)
    {
        $this->_logger = $logger;
    }
    
    public function getLogger()
    {
        return $this->_logger;
    }
    
    public function addRoutingMasks($masks) 
    {
        if (is_array($masks)) {
            foreach ($masks as $mask => $regex) {
                $this->addRoutingMask($mask, $regex);
            }
        }
    }
    
    public function addRoutingMask($mask, $regex) 
    {
        if (preg_match('~^[a-z0-9_]+$~', $mask)) {
            $this->_routingMasks[$mask] = $regex;
        }
    }
    
}

return \eternal\Framework::getInstance();
