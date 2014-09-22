<?php
return function ($string, $length = 80, $etc = '...', $breakWords = FALSE, $middle = FALSE) {
    if ($length < 1) {
        return '';
    }

    if (isset($string[$length])) {
        $length -= min($length, strlen($etc));
        if (!($breakWords and $middle)) {
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
        }
        if (!$middle) {
            return substr($string, 0, $length) . $etc;
        }
        return substr($string, 0, $length / 2) . $etc . substr($string, - $length / 2);
    }
    return $string;
};
