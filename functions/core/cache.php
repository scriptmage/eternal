<?php
return function & () 
{
    $cache = new eternal\components\Cache($this);
    return $cache;
};
