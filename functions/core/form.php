<?php
return function & ($name, $method = 'POST') 
{
    $form = new eternal\components\Form($this, $name, $method);
    return $form;
};
