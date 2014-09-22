<?php
return function($buffer) {
    $buffer = preg_replace('~/\*[^*]*\*+([^/][^*]*\*+)*/~', '', $buffer);
    $buffer = preg_replace('~[\r\n\t]+~', '', $buffer);
    $buffer = preg_replace("~ {2,}~", ' ', $buffer);
    $buffer = preg_replace('~> +<~', '><', $buffer);
    return $buffer;
};
