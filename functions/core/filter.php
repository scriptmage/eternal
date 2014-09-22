<?php
return function & ($data) 
{
    $filter = new eternal\components\Filter($data);
    return $filter;
};
