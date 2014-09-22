<?php

class AutoLoader
{

    protected static $_paths = array();

    public static function addPath($path)
    {
        $path = ltrim(realpath($path) . '/');
        if ($path) {
            self::$_paths[] = $path;
        }
    }

    public static function load($className)
    {
        $fileName = '';
        $namespace = '';
        $className = ltrim($className, '\\');
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $classPath = $fileName;
        foreach (self::$_paths as $path) {
            $file = $path . strtolower($classPath);
            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }

}

spl_autoload_register(array('AutoLoader', 'load'));
