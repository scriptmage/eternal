<?php
return function($string, $flags = ENT_COMPAT, $encoding = 'UTF-8', $doubleEncode = TRUE) {
    return htmlspecialchars($string, $flags, $encoding, $doubleEncode);
};
