<?php
return function ($uri = '', $callback = null, $httpResponseCode = 302, $method = 'location') {
    $redirect = TRUE;
    if (is_callable($callback)) {
        $redirect = call_user_func($callback);
    }

    if ($redirect === FALSE) {
        return;
    }

    if (!preg_match('~^https?://~i', $uri)) {
        $uri = sprintf("%s://%s%s", $this->_protocol, $this->server->HTTP_HOST, $uri);
    }

    switch ($method) {
        case 'refresh':
            header("Refresh:0;url=" . $uri);
            break;
        default:
            header("Location: " . $uri, TRUE, $httpResponseCode);
    }
    exit;
};
