<?php
return function($address, $text = '', $params = array()) {
    $default = array(
        'encode' => 'none',
        'extra' => ''
    );

    $params = array_merge($default, $params);
    switch ($params['encode']) {
        case 'js':
        case 'javascript':
            $str = sprintf('document.write(\'<a href="mailto:%s" %s>%s</a>\');', $address, $params['extra'], $text);
            for ($jsEncode = '', $x = 0, $_length = strlen($str); $x < $_length; $x++) {
                $jsEncode .= '%' . bin2hex($str[$x]);
            }
            $str = '<script type="text/javascript">eval(unescape(\'' . $jsEncode . '\'))</script>';
            break;
        default:
            $str = '<a href="mailto:' . $address . '" ' . $params['extra'] . '>' . $text . '</a>';
    }

    return $str;
};
