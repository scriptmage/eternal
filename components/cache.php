<?php

namespace eternal\components;

class Cache extends \stdClass
{

    private
        $expire = 3600,
        $dir = NULL;

    public function __construct(&$app)
    {
        $this->dir = $app->dir($app->config->folders->cache);
    }

    public function expire($time = NULL)
    {
        if (is_null($time)) {
            return $this->expire;
        }
        $this->expire = $time;
    }

    public function dir($folder = NULL)
    {
        if (is_null($folder)) {
            return $this->dir->root();
        }
        $this->dir->root($folder);
    }

    public function clear()
    {
        return $this->dir->del('/', TRUE);
    }

    public function set($key, $data)
    {
        if (preg_match('/^(?:[a-z0-9_-]|\.(?!\.))+$/iD', $key)) {
            if (($f = fopen($this->dir->root() . $key . '.cache', 'w')) !== FALSE) {

                if (!is_string($data)) {
                    $data = serialize($data);
                }

                fwrite($f, $data);
                return fclose($f);
            }
        }
        return FALSE;
    }

    public function get($key, $expire = NULL)
    {
        if (preg_match('/^(?:[a-z0-9_-]|\.(?!\.))+$/iD', $key)) {
            $file = $this->dir->root() . $key . '.cache';

            if (is_null($expire)) {
                $expire = $this->expire;
            }

            if (abs(filemtime($file) - date('U')) >= $expire) {
                return FALSE;
            }

            if (($f = fopen($file, 'r')) !== FALSE) {
                $buff = fread($f, filesize($file));
                fclose($f);

                if ($data = unserialize($buff)) {
                    $buff = $data;
                }

                return $buff;
            }
        }
        return FALSE;
    }

}
