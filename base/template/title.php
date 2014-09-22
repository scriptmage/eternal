<?php

namespace eternal;

class Base_Template_Title
{

    private $_delimiter = '';
    private $_title = array();

    public function & prefix($prefix, $delimiter = NULL)
    {
        if (!is_null($delimiter)) {
            $this->delimiter($delimiter);
        }
        $this->_title[0] = $prefix;
        return $this;
    }

    public function get($delimiter = NULL)
    {
        if (is_null($delimiter)) {
            $delimiter = $this->_delimiter;
        }
        return implode($delimiter, $this->_title);
    }

    public function & set($title = '')
    {
        $this->_title[1] = $title;
        return $this;
    }

    public function & postfix($postfix, $delimiter = NULL)
    {
        if (!is_null($delimiter)) {
            $this->delimiter($delimiter);
        }
        $this->_title[2] = $postfix;
        return $this;
    }

    public function & delimiter($delimiter)
    {
        $this->_delimiter = htmlspecialchars($delimiter);
        return $this;
    }

}
