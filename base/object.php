<?php

namespace eternal;

class Base_Object extends \stdClass
{

    protected $_app = NULL;

    public function __construct(Framework &$app)
    {
        $this->_app = $app;
    }

}
