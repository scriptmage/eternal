<?php

namespace eternal;

interface Base_Log_Writer {
	public function write($app, $level, $message, $context);
}