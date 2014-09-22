<?php
return function($data, $key) {
    $key = is_null($key) ? $this->config->secure->encrypt->key : $key;
    $key = hash('sha256', $key, TRUE);
    if (function_exists('mcrypt_encrypt')) {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $key));
    }

    $result = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $char = substr($data, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) + ord($keychar));
        $result .= $char;
    }

    return base64_encode($result);
};
