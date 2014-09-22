<?php

namespace eternal;

/**
 * Describes a logger-aware instance
 */
interface Base_Log_IAware
{

    /**
     * Sets a logger instance on the object
     *
     * @param Base_Log_Interface $logger
     * @return null
     */
    public function setLogger(Base_Log_Interface $logger);

    /**
     * Gets a logger instance on the object
     *
     * @param Base_Log_Interface $logger
     * @return Base_Log_Interface
     */
    public function getLogger();
}
