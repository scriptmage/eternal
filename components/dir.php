<?php

namespace eternal\components;

class Dir extends \stdClass
{

    private $_root = '';
    private $_umask = NULL;

    private function fix($directory)
    {
        $directory = trim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return preg_replace(sprintf('~%s{2,}~', DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR, $this->_root . $directory);
    }

    public function __construct($directory)
    {
        $this->root($directory);
    }

    public function root($directory = NULL)
    {
        if (is_null($directory)) {
            return $this->_root;
        }
        $this->_root = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function read($directory)
    {
        if (!$this->exists($directory)) {
            return FALSE;
        }
        $directory = $this->fix($directory);
        $dhandle = opendir($directory);
        $files = array();

        if ($dhandle) {
            while (FALSE !== ($fname = readdir($dhandle))) {
                if (!in_array($fname, array('.', '..'))) {
                    $files[] = $fname;
                }
            }
            closedir($dhandle);
        }
        return $files;
    }

    public function del($directory, $onlyEmpty = FALSE)
    {
        if (!$this->exists($directory)) {
            return FALSE;
        }
        $directory = $this->fix($directory);

        $iterator = new RecursiveDirectoryIterator($directory);
        foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isDir()) {
                $ret = rmdir($file->getPathname());
            } else {
                $ret = unlink($file->getPathname());
            }
            if (!$ret) {
                return FALSE;
            }
        }

        if ($onlyEmpty) {
            return TRUE;
        } else {
            return rmdir($directory);
        }
    }

    public function make($pathname, $mode = 0777, $recursive = FALSE, $context = NULL)
    {
        if (is_null($context)) {
            $ret = mkdir($this->fix($pathname), $mode, $recursive);
        } else {
            $ret = mkdir($this->fix($pathname), $mode, $recursive, $context);
        }
        if (!is_null($this->_umask)) {
            umask($this->_umask);
        }
        return $ret;
    }

    public function exists($directory)
    {
        $directory = $this->fix($directory);
        if (!file_exists($directory) or ! is_dir($directory)) {
            return FALSE;
        }
        return TRUE;
    }

    public function & umask($mask)
    {
        $this->_umask = umask($mask);
        return $this;
    }

}
