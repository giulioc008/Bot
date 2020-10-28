<?php
/**
 * This file contains the source code of the BotCommand object.
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

/**
 * @link https://core.telegram.org/bots/api#botcommand The BotCommand object.
 *
 * @package src\types
 */
class BotCommand {
	/**
	 * @var string $command The name of the command.
	 */
	private string $command;
	/**
	 * @var string $description The description of the command.
	 */
	private string $description;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param string $command		The name of the command.
	 * @param string $description	The description of the command.
	 *
	 * @throws InvalidArgumentException If the name of the command is more length of 32 characters.
	 * @throws InvalidArgumentException If the description of the command is less length of 3 characters or more length of 256 characters.
	 *
	 * @return void
	 */
	public function __construct(string $command, string $description) {
		/**
		 * Checking if the name of the command respect the constraints
		 *
		 * strlen() return the length of the string
		 */
		if (strlen($command) < 1) {
			throw new InvalidArgumentException('The name of the command is empty.');
		} else if (strlen($command) > 32) {
			throw new InvalidArgumentException('The name of the command is more length of 32 characters.');
		}

		/**
		 * Checking if the description of the command respect the constraints
		 *
		 * strlen() return the length of the string
		 */
		if (strlen($description) < 3) {
			throw new InvalidArgumentException('The description of the command is less length of 3 characters.');
		} else if (strlen($description) > 256) {
			throw new InvalidArgumentException('The description of the command is more length of 256 characters.');
		}

		$this -> command = $command;
		$this -> description = $description;
	}

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	public function __debugInfo() : array {
		return [
			'command' => $this -> command,
			'description' => $this -> description
		];
	}

	/**
	 * @internal Retrieve a property of the class.
	 *
	 * @param string $name The name of the property.
	 *
	 * @return mixed
	 */
	public function __get(string $name) {
		switch ($name) {
			case 'command':
				return $this -> command;
			case 'description':
				return $this -> description;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses BotCommand::__construct to create the class.
	 *
	 * @param string $command		The name of the command.
	 * @param string $description	The description of the command.
	 *
	 * @throws InvalidArgumentException If the name of the command is more length of 32 characters.
	 * @throws InvalidArgumentException If the description of the command is less length of 3 characters or more length of 256 characters.
	 *
	 * @return mixed
	 */
	public function __invoke(string $command, string $description) {
		$this -> __construct($command, $description);
	}

	/**
	 * @internal Determine if the object is empty or not.
	 *
	 * @param string $name The name of the property.
	 *
	 * @return bool
	 */
	public function __isset(string $name) : bool {
		/**
		 * Checking if the property is setted
		 *
		 * empty() check if the argument is empty
		 * 	''
		 * 	""
		 * 	'0'
		 * 	"0"
		 * 	0
		 * 	0.0
		 * 	NULL
		 * 	FALSE
		 * 	[]
		 * 	array()
		 */
		switch ($name) {
			case 'command':
				return empty($this -> command) === FALSE || $this -> command === '0';
			case 'description':
				return empty($this -> description) === FALSE || $this -> description === '0';
		}
	}

	/**
	 * @internal Set a property of the class.
	 *
	 * @param string	$name 	The name of the property.
	 * @param mixed 	$value	The value of the property.
	 *
	 * @throws InvalidArgumentException If the property don't respect its constraints.
	 *
	 * @return void
	 */
	public function __set(string $name, $value) {
		switch ($name) {
			case 'command':
				/**
				* Checking if the name of the command respect the constraints
				*
				* strlen() return the length of the string
				*/
				if (strlen($command) < 1) {
					throw new InvalidArgumentException('The name of the command is empty.');
				} else if (strlen($command) > 32) {
					throw new InvalidArgumentException('The name of the command is more length of 32 characters.');
				}

				$this -> command = $value;
			case 'description':
				/**
				* Checking if the description of the command respect the constraints
				*
				* strlen() return the length of the string
				*/
				if (strlen($description) < 3) {
					throw new InvalidArgumentException('The description of the command is less length of 3 characters.');
				} else if (strlen($description) > 256) {
					throw new InvalidArgumentException('The description of the command is more length of 256 characters.');
				}

				$this -> description = $value;
		}
	}

	/**
	 * @internal Return a string version of the object.
	 *
	 * @return string
	 */
	public function __toString() : string {
		/**
		 * Converting the object to a string
		 *
		 * json_encode() convert the PHP object to a JSON string
		 */
		return json_encode($this -> __debugInfo(), JSON_UNESCAPED_SLASHES);
	}
}
