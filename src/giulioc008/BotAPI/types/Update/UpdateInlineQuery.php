<?php
/**
 * This file contains the source code of the Update object relative to the InlineQuery.
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
use giulioc008\BotAPI\types\InlineMode\InlineQuery;

/**
 * @link https://core.telegram.org/bots/api#update The Update object relative to the InlineQuery.
 *
 * @package giulioc008\BotAPI\types\Update
 */
class UpdateInlineQuery extends Update {
	/**
	* @var int $id The id of the update.
	*/
	private int $id;
	/**
	* @var InlineQuery $inline_query New incoming inline query.
	*/
	private InlineQuery $inline_query;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param $id					int			The id of the update.
	 * @param $inline_query			InlineQuery	New incoming inline query.
	 *
	 * @return void
	 */
	public function __construct(int $id, InlineQuery $inline_query) {
		$this -> inline_query = $inline_query;
	}

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	public function __debugInfo() : array {
		return [
			'id' => $this -> id,
			'inline_query' => $this -> inline_query
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
			case 'inline_query':
				return $this -> inline_query;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses UpdateInlineQuery::__construct to create the class.
	 *
	 * @param $id					int			The id of the update.
	 * @param $inline_query			InlineQuery	New incoming inline query.
	 *
	 * @return mixed
	 */
	public function __invoke(int $id, InlineQuery $inline_query) {
		$this -> __construct($id, $inline_query);
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
			case 'inline_query':
				return empty($this -> inline_query) === FALSE;
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
			case 'inline_query':
				$this -> inline_query = $value;
		}
	}
}
