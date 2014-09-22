<?php
return function($var, $value) {
    return (isset($var) and ! empty($var)) ? $var : $value;
};
