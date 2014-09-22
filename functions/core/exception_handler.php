<?php
return function($e) {
    if ($this->debug) {
        echo <<< STYLE
<style type="text/css">
    div.debug-message {
        width: 800px; text-align: center; margin: 10 auto;
    }
    div.debug-message table {
        width: 100%;
    }
    div.debug-message table tr:nth-child(even) {
        background-color: rgb(230,230,230);
    }
    div.debug-message table tr:nth-child(odd) {
        background-color: rgb(240,240,240);
    }
    div.debug-message table tr th {
        width: 100px; font-family: verdana !important; padding: 10px; font-weight: bold; text-align: center;
    }
    div.debug-message table tr td {
        font-family: verdana !important;
    }
</style>
STYLE;

        echo '<div class="debug-message"><table>';
        echo sprintf('<tr><th>Code</th><td>%s</td></tr>', $e->getCode());
        echo sprintf('<tr><th>Message</th><td>%s</td></tr>', $e->getMessage());
        echo sprintf(
            '<tr><th>File</th><td>%s</td></tr>', 
            substr(
                $e->getFile(),
                strpos($e->getFile(), $this->server->DOCUMENT_ROOT) + strlen($this->server->DOCUMENT_ROOT)
            )
        );
        echo sprintf('<tr><th>Line</th><td>%s</td></tr>', $e->getLine());
        echo '</table></div>';
    } else {
        file_put_contents(
            sprintf('%serror.log', $this->config->folders->log),
            sprintf("%s [%d] %s %s [%d]%s", date('Y-m-d H:i:s'), $e->getCode(), $e->getMessage(), $e->getFile(),
                $e->getLine(), PHP_EOL
            ), FILE_APPEND
        );
    }
};
