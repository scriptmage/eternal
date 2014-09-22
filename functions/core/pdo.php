<?php
return function & ($optDriver = array()) {
    static $dblink = NULL;
    if (!$dblink) {
        $optDefault = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config->database->charset}",
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_PERSISTENT => TRUE
        );
        $optDriver = array_merge($optDefault, $optDriver);
        $dsn = sprintf('%s:host=%s;dbname=%s;port=%d', $this->config->database->provider, $this->config->database->host,
            $this->config->database->name, $this->config->database->port
        );
        $dblink = new eternal\components\DB_Wrapper($dsn, $this->config->database->username,
            $this->config->database->password, $optDriver
        );
    }
    return $dblink;
};
