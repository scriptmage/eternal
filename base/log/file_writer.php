<?php

namespace eternal;

use Base_Log_Writer;

class File_Writer implements Base_Log_Writer {
	public function writer($app, $level, $message, $context) {
		if (($requestUri = $app->server->REQUEST_URI) == '') {
			$requestUri = "REQUEST_URI_UNKNOWN";
		}
		
		$logfile = sprintf ( '%s%s.log', $app->config->folders->log, $level );
		
		$date = date ( "Y-m-d H:i:s" );
		if ($fd = @fopen ( $logfile, "a" )) {
			$result = fputcsv ( $fd, array (
					$date,
					$app->ip (),
					$requestUri,
					$context 
			), "\t" );
			fclose ( $fd );
			
			if ($result > 0) {
				return array (
						status => true 
				);
			} else {
				return array (
						status => false,
						message => 'Unable to write to ' . $logfile . '!' 
				);
			}
		} else {
			return array (
					status => false,
					message => 'Unable to open log ' . $logfile . '!' 
			);
		}
	}
}