<?php
return function ($data, $code = 200) {
    header('Content-type: application/json', TRUE, $code);
    echo json_encode($data);
    exit;
};
