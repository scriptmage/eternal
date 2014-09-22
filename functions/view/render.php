<?php
return function($template) {
    $extension = '.' . $this->_app->config->template->extension;
    if (pathinfo($template, PATHINFO_EXTENSION) == $this->_app->config->template->extension) {
        $extension = '';
    }
    $fileTemplate = sprintf('%s%s%s', $this->_app->config->folders->template, $template, $extension);
    if (file_exists($fileTemplate)) {
        extract($this->_vars);
        extract($this->_alwaysLoaded);
        include($fileTemplate);

        if ($this->_app->config->template->compress and is_callable(array($this, 'compress'))) {
            return $this->compress(ob_get_clean());
        }

        return ob_get_clean();
    }
    throw new Exception(sprintf('Template is not found: <strong>%s</strong>', $fileTemplate));
};
