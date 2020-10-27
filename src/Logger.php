<?php
/**
* This file contains the logger's source code.
* No libraries are used in this project.
*
* @author		Giulio Coa
*
* @copyright	2020- Giulio Coa <34110430+giulioc008@users.noreply.github.com>
*
* @license		https://choosealicense.com/licenses/lgpl-3.0/
*/


class Logger {
	/**
	* The log level of the Logger
	*
	* Numerical		  Severity
	*   Code
	*
	*	0		FATAL_ERROR: Indicates a fatal error
	*	1		ERROR: Indicates a recoverable error
	*	2		WARNING: Indicates a warning
	*	3		NOTICE: Indicates an info message
	*	4		VERBOSE: Indicates a verbose info message
	*	5		ULTRA_VERBOSE: Indicates a ultra verbose info message
	*/
	const FATAL_ERROR = 0;
	const ERROR = 1;
	const WARNING = 2;
	const NOTICE = 3;
	const VERBOSE = 4;
	const ULTRA_VERBOSE = 5;

	private static $printed = FALSE;

	private int $level;
	private string $path;
	private resource $output;

	/**
	* The constructor of the class.
	*
	* @param string $path The path of the file where we want log.
	* @param int $level [Optional] The log level of the Logger.
	*
	* @return void
	*/
	public function __construct(string $path, int $level = self::NOTICE) {
		$this -> level = $level;

		/**
		* Retrieve the absolute path of the file
		*
		* getcwd() gets the current working directory
		*/
		$this -> path = ($path[0] ?? '') !== '/' ? getcwd() . DIRECTORY_SEPARATOR . $path : $path;

		/**
		* Checking if the path contains the extension
		*
		* preg_match() perform a RegEx match
		*/
		if (preg_match('/^.+\.log$/mu', $this -> path) === FALSE) {
			$this -> path .= '.log';
		}

		/**
		* Open the log file
		*
		* fopen() open a file
		*/
		$this -> output = fopen($this -> path, 'a');
	}

	/**
	* The destructors of the class.
	*
	* @return void
	*/
	public function __destruct() {
		/**
		* Close the log file
		*
		* fclose() close a file
		*/
		fclose($this -> output);
	}

	/**
	* Log a message.
	*
	* @param mixed $message The message to log.
	* @param int $level [Optional] The log level of the message.
	*
	* @return void
	*/
	public function logger($message, int $level = self::NOTICE) {
		// Checking if the message must be logged
		if ($level > $this -> level) {
			return;
		}

		// Checking if the intestation was already printed
		if (self::$printed === FALSE) {
			self::$printed = TRUE;

			// Write the intestation of the log file
			$this -> logger('Bot');
			$this -> logger('Copyright (C) 2020- Giulio Coa');
			$this -> logger('Licensed under LGPLv3');
			$this -> logger('https://github.com/giulioc008/Bot');
		}

		/**
		* Converting the message into a printable message
		*
		* is_string() check if thee type of the argument is 'string'
		* json_encode() convert the PHP object to a JSON string
		*/
		if ($message instanceof Throwable) {
			$message = (string) $message;
		} elseif (is_string($message) === FALSE) {
			$message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}

		/**
		* Retrieve the new log
		*
		* debug_backtrace() generates a backtrace
		* basename() returns trailing name component of path
		* str_pad() pad a string to a certain length with another string
		*/
		$file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
		$file = $file[0]['file'];
		$file = basename($file, '.php');
		$param = str_pad($file . ': ', 16) . "\t" . $param . "\n";

		/**
		* Append the log to the file
		*
		* fwrite() write data to a file
		* file_put_contents() write data to a file
		*/
		if (fwrite($this -> output, $param) === FALSE) {
			file_put_contents($this -> path, $param, FILE_APPEND);
		}
	}
}
