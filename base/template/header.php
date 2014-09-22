<?php

namespace eternal;

class Base_Template_Header
{

    private $_head = array();
    private $_body = array();
    private $_script = array();
    private $_css = array();
    private $_meta = array();

    public function __construct()
    {
        $this->title = new Base_Template_Title;
    }

    public function meta($name = NULL, $content = '', $metaName = 'name')
    {
        if (is_null($name)) {
            return implode("\n", $this->_meta);
        }

        if (is_array($name)) {
            $metaData = $name;
            foreach ($metaData as $name => $content) {
                $this->_meta[$name] = sprintf(
                    '<meta %s="%s" content="%s" />', htmlspecialchars($metaName), htmlspecialchars($name),
                    htmlspecialchars($content)
                );
            }
        } else {
            $this->_meta[$name] = sprintf(
                '<meta %s="%s" content="%s" />', htmlspecialchars($metaName), htmlspecialchars($name),
                htmlspecialchars($content)
            );
        }
    }

    public function css($url = NULL, $attrs = array())
    {
        if (is_null($url)) {
            return implode("\n", $this->_css);
        }

        $defaultAttrs = array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'media' => 'screen'
        );

        $strAttrs = '';
        foreach (array_merge($defaultAttrs, $attrs) as $name => $value) {
            $strAttrs .= sprintf(' %s="%s"', htmlspecialchars($name), htmlspecialchars($value));
        }

        if (is_array($url)) {
            foreach ($url as $file) {
                $this->_css[] = sprintf('<link href="%s"%s />', htmlspecialchars($file), $strAttrs);
            }
        } else {
            $this->_css[] = sprintf('<link href="%s"%s />', htmlspecialchars($url), $strAttrs);
        }
    }

    public function script($url = NULL, $attrs = array())
    {
        if (is_null($url)) {
            return implode("\n", $this->_script);
        }

        $defaultAttrs = array(
            'type' => 'text/javascript',
        );

        $strAttrs = '';
        foreach (array_merge($defaultAttrs, $attrs) as $name => $value) {
            $strAttrs .= sprintf(' %s="%s"', htmlspecialchars($name), htmlspecialchars($value));
        }

        if (is_array($url)) {
            foreach ($url as $file) {
                $this->_script[] = sprintf('<script src="%s"%s></script>', htmlspecialchars($file), $strAttrs);
            }
        } else {
            $this->_script[] = sprintf('<script src="%s"%s></script>', htmlspecialchars($url), $strAttrs);
        }
    }

    public function head($string = NULL)
    {
        if (is_null($string)) {
            return implode("\n", $this->_head);
        }
        $this->_head[] = $string;
    }

    public function body($string = NULL)
    {
        if (is_null($string)) {
            return implode("\n", $this->_body);
        }
        $this->_body[] = $string;
    }

    public function title($title = NULL)
    {
        if (is_null($title)) {
            return $this->title->get();
        }
        $this->title->set(htmlspecialchars($title));
    }

}
