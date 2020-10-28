<?php
/**
 * This file contains the source code of the Update object relative to an edited message.
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

// Adding the necessary classes
require_once('../Message.php');
require_once('Update.php');

/**
 * @link https://core.telegram.org/bots/api#update The Update object relative to an edited message.
 *
 * @package src\types\Update
 */
class UpdateEditMessage extends Update {
	/**
	* @var int $id The update's unique identifier.
	*/
	private int $id;
	/**
	* @var Message $edited_message New version of a message that is known to the bot and was edited.
	*/
	private Message $edited_message;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param $id					int			The update's unique identifier.
	 * @param $edited_message		Message		New version of a message that is known to the bot and was edited.
	 *
	 * @return void
	 */
	public function __construct(int $id, Message $edited_message) {
		$this -> id = $id;
		$this -> edited_message = $edited_message;
	}

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	public function __debugInfo() : array {
		return [
			'id' => $this -> id,
			'edited_message' => $this -> edited_message
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
			case 'id':
				return $this -> id;
			case 'edited_message':
				return $this -> edited_message;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses UpdateEditMessage::__construct to create the class.
	 *
	 * @param $id					int			The update's unique identifier.
	 * @param $edited_message		Message		New version of a message that is known to the bot and was edited.
	 *
	 * @return mixed
	 */
	public function __invoke(int $id, Message $edited_message) {
		$this -> __construct($id, $edited_message);
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
			case 'id':
				return empty($this -> id) === FALSE;
			case 'edited_message':
				return empty($this -> edited_message) === FALSE;
		}
	}

	/**
	 * @internal Set a property of the class.
	 *
	 * @param string	$name 	The name of the property.
	 * @param mixed 	$value	The value of the property.
	 *
	 * @return void
	 */
	public function __set(string $name, $value) {
		switch ($name) {
			case 'id':
				$this -> id = $value;
			case 'edited_message':
				$this -> edited_message = $value;
		}
	}
}
