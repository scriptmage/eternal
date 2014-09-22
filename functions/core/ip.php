<?php
return function ($options = array()) {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $property) {
        if (!@empty($this->server->{$property})) {
            return filter_var($this->server->{$property}, FILTER_VALIDATE_IP, $options);
        }
    }
    return 'UNKNOWN';
};
