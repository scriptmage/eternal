<?php
return function() {
    $error = error_get_last();
    if ($error["type"] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
        $this->error_handler($error["type"], $error["message"], $error["file"], $error["line"]);
    }
};
