<?php

namespace eternal;

/**
 * This is a simple Logger implementation that other Loggers can inherit from.
 *
 * It simply delegates all log-level-specific methods to the `log` method to
 * reduce boilerplate code that a simple Logger that does the same thing with
 * messages regardless of the error level has to implement.
 */
class Base_Logger extends Base_Object implements Base_Log_Interface
{

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        return $this->log(Base_log_Level::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        return $this->log(Base_log_Level::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        return $this->log(Base_log_Level::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        return $this->log(Base_log_Level::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        return $this->log(Base_log_Level::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        return $this->log(Base_log_Level::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        return $this->log(Base_log_Level::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        return $this->log(Base_log_Level::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        if (($requestUri = $this->_app->server->REQUEST_URI) == '') {
            $requestUri = "REQUEST_URI_UNKNOWN";
        }
        
        $logfile = sprintf('%s%s.log', $this->_app->config->folders->log, $level);

        $date = date("Y-m-d H:i:s");
        if ($fd = @fopen($logfile, "a")) {
            $result = fputcsv(
                $fd, 
                array($date, $this->_app->ip(), $requestUri, $this->interpolate($message, $context)), 
                "\t"
            );
            fclose($fd);

            if ($result > 0) {
                return array(status => true);
            } else {
                return array(status => false, message => 'Unable to write to ' . $logfile . '!');
            }
        } else {
            return array(status => false, message => 'Unable to open log ' . $logfile . '!');
        }
    }

    /**
     * Interpolates context values into the message placeholders.
     * @param type $message
     * @param array $context
     * @return type
     */
    public function interpolate($message, array $context = array())
    {
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        return strtr($message, $replace);
    }

}
