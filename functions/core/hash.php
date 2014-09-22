<?php
return function($value, $hash) {
    if (is_null($hash)) {
        return crypt($value, $this->config->secure->encrypt->key);
    }
    return crypt($value, $hash) == $hash;
};
