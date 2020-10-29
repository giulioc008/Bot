<?php
/**
 * This file contains the source code of the Update object.
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

namespace giulioc008\BotAPI\types\Update;

/**
 * @link https://core.telegram.org/bots/api#update The Update object.
 *
 * @package giulioc008\BotAPI\types\Update
 */
abstract class Update {
	/**
	 * @internal The constructor of the abstract class.
	 *
	 * @param $id int The update's unique identifier.
	 *
	 * @return void
	 */
	abstract public function __construct(int $id);

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	abstract public function __debugInfo() : array;

	/**
	 * @internal Retrieve a property of the class.
	 *
	 * @param string $name The name of the property.
	 *
	 * @return mixed
	 */
	abstract public function __get(string $name);

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @param $id int The update's unique identifier.
	 *
	 * @return mixed
	 */
	abstract public function __invoke(int $id);

	/**
	 * @internal Determine if the object is empty or not.
	 *
	 * @param string $name The name of the property.
	 *
	 * @return bool
	 */
	abstract public function __isset(string $name) : bool;

	/**
	 * @internal Set a property of the class.
	 *
	 * @param string	$name 	The name of the property.
	 * @param mixed 	$value	The value of the property.
	 *
	 * @return void
	 */
	abstract public function __set(string $name, $value);

	/**
	 * @internal Return a string version of the object.
	 *
	 * @uses Update::__debugInfo to retrieve an array version of the class.
	 *
	 * @return string
	 */
	public function __tostring() : string {
		/**
		 * Converting the object to a string
		 *
		 * json_encode() convert the PHP object to a JSON string
		 */
		return json_encode($this -> __debugInfo(), JSON_UNESCAPED_SLASHES);
	}
}
