<?php
/**
 * This file contains the source code of the Update object relative to the CallbackQuery.
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

// Adding the necessary classes
use giulioc008\BotAPI\types\Update\Update;
use giulioc008\BotAPI\types\CallbackQuery;

/**
 * @link https://core.telegram.org/bots/api#update The Update object relative to the CallbackQuery.
 *
 * @package giulioc008\BotAPI\types\Update
 */
class UpdateCallbackQuery extends Update {
	/**
	* @var int $id The id of the update.
	*/
	private int $id;
	/**
	* @var CallbackQuery $callback_query New incoming callback query.
	*/
	private CallbackQuery $callback_query;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param $id					int				The id of the update.
	 * @param $callback_query		CallbackQuery	New incoming callback query.
	 *
	 * @return void
	 */
	public function __construct(int $id, CallbackQuery $callback_query) {
		$this -> id = $id;
		$this -> callback_query = $callback_query;
	}

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	public function __debugInfo() : array {
		return [
			'id' => $this -> id,
			'callback_query' => $this -> callback_query
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
			case 'callback_query':
				return $this -> callback_query;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses UpdateCallbackQuery::__construct to create the class.
	 *
	 * @param $id					int				The id of the update.
	 * @param $callback_query		CallbackQuery	New incoming callback query.
	 *
	 * @return mixed
	 */
	public function __invoke(int $id, CallbackQuery $callback_query) {
		$this -> __construct($id, $callback_query);
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
			case 'callback_query':
				return empty($this -> callback_query) === FALSE;
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
			case 'callback_query':
				$this -> callback_query = $value;
		}
	}
}
