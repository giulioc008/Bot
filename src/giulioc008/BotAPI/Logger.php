<?php
/**
 * This file contains the logger's source code.
 * No libraries are used in this project.
 *
 * @author		Giulio Coa
 *
 * @copyright	2020- Giulio Coa
 *
 * @license		https://choosealicense.com/licenses/lgpl-3.0/ LGPL version 3
 */

declare(encoding='UTF-8');
declare(strict_types=1);

namespace giulioc008\BotAPI;

/**
 * @package giulioc008\BotAPI
 */
class Logger {
	/**
	 * @internal Fatal error log's level.
	 */
	const FATAL_ERROR = 0;
	/**
	 * @internal Error log's level.
	 */
	const ERROR = 1;
	/**
	 * @internal Warning log's level.
	 */
	const WARNING = 2;
	/**
	 * @internal Notice log's level.
	 */
	const NOTICE = 3;
	/**
	 * @internal Verbose log's level.
	 */
	const VERBOSE = 4;
	/**
	 * @internal Ultra verbose log's level.
	 */
	const ULTRA_VERBOSE = 5;
	/**
	 * @var const LEVEL_FATAL_ERROR Fatal error log's level.
	 */
	const LEVEL_FATAL_ERROR = self::FATAL_ERROR;
	/**
	 * @var const LEVEL_ERROR Error log's level.
	 */
	const LEVEL_ERROR = self::ERROR;
	/**
	 * @var const LEVEL_WARNING Warning log's level.
	 */
	const LEVEL_WARNING = self::WARNING;
	/**
	 * @var const LEVEL_NOTICE Notice log's level.
	 */
	const LEVEL_NOTICE = self::NOTICE;
	/**
	 * @var const LEVEL_VERBOSE Verbose log's level.
	 */
	const LEVEL_VERBOSE = self::VERBOSE;
	/**
	 * @var const LEVEL_ULTRA_VERBOSE Ultra verbose log's level.
	 */
	const LEVEL_ULTRA_VERBOSE = self::ULTRA_VERBOSE;

	/**
	 * @internal Tells if the intestation of the log file was already printed.
	 */
	private static $printed = FALSE;

	/**
	 * @var int $level The log level of the Logger.
	 */
	private int $level;
	/**
	 * @var string $path The path of the file where we want log.
	 */
	private string $path;
	/**
	 * @var resource $output The log file.
	 */
	private resource $output;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param string $path	The path of the file where we want log.
	 * @param int	 $level The log level of the Logger.
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
	 * @internal The destructors of the class.
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
	 * @uses Logger::$level to check if the message must be logged.
	 * @uses Logger::$output to write the log.
	 *
	 * @param mixed $message	The message to log.
	 * @param int 	$level		The log level of the message.
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
		$message = str_pad($file . ': ', 16) . "\t" . $message . "\n";

		/**
		 * Append the log to the file
		 *
		 * fwrite() write data to a file
		 * file_put_contents() write data to a file
		 */
		if (fwrite($this -> output, $message) === FALSE) {
			file_put_contents($this -> path, $message, FILE_APPEND);
		}
	}
}
