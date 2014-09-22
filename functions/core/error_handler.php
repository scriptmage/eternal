<?php
return function($errno, $errstr, $errfile, $errline) {
    if (!($errno & $this->debug)) {
        return TRUE;
    }

    $this->exception_handler(new ErrorException($errstr, $errno, 0, $errfile, $errline));
    return TRUE;
};
