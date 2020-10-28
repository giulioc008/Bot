<?php
/**
 * This file contains the source code of the InlineQuery object.
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

// Adding the necessary class
require_once('../User.php');

/**
 * @link https://core.telegram.org/bots/api#inlinequery The InlineQuery object.
 *
 * @package src\types\InlineMode
 */
class InlineQuery {
	/**
	 * @var string $id The id of the query.
	 */
	private string $id;
	/**
	 * @var User $from The sender of the query.
	 */
	private User $from;
	/**
	 * @var string $query The text of the query.
	 */
	private string $query;
	/**
	 * @var string $offset The offset of the results to be returned.
	 */
	private string $offset;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param string	$id		The id of the query.
	 * @param User		$from	The sender of the query.
	 * @param string	$query	The text of the query.
	 * @param string	$offset	The offset of the results to be returned.
	 *
	 * @throws InvalidArgumentException If the text of the query is more length of 256 characters.
	 *
	 * @return void
	 */
	public function __construct(string $id, User $from, string $query, string $offset) {
		/**
		 * Checking if the text of the query respect the constraints
		 *
		 * strlen() return the length of the string
		 */
		if (strlen($query) < 1) {
			throw new InvalidArgumentException('The text of the query is empty.');
		} else if (strlen($query) > 256) {
			throw new InvalidArgumentException('The text of the query is more length of 256 characters.');
		}

		$this -> id = $id;
		$this -> from = $from;
		$this -> query = $query;
		$this -> offset = $offset;
	}

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	public function __debugInfo() : array {
		return [
			'id' => $this -> id,
			'from' => $this -> from,
			'query' => $this -> query,
			'offset' => $this -> offset
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
			case 'from':
				return $this -> from;
			case 'query':
				return $this -> query;
			case 'offset':
				return $this -> offset;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses InlineQuery::__construct to create the class.
	 *
	 * @param string	$id		The id of the query.
	 * @param User		$from	The sender of the query.
	 * @param string	$query	The text of the query.
	 * @param string	$offset	The offset of the results to be returned.
	 *
	 * @throws InvalidArgumentException If the text of the query is more length of 256 characters.
	 *
	 * @return mixed
	 */
	public function __invoke(string $id, User $from, string $query, string $offset) {
		$this -> __construct($id, $from, $query, $offset);
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
				return empty($this -> id) === FALSE || $this -> id === '0';
			case 'from':
				return empty($this -> from) === FALSE;
			case 'query':
				return empty($this -> query) === FALSE || $this -> query === '0';
			case 'offset':
				return empty($this -> offset) === FALSE || $this -> offset === '0';
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
			case 'id':
				$this -> id = $value;
			case 'from':
				$this -> from = $value;
			case 'query':
				/**
				* Checking if the text of the query respect the constraints
				*
				* strlen() return the length of the string
				*/
				if (strlen($value) < 1) {
					throw new InvalidArgumentException('The text of the query is empty.');
				} else if (strlen($value) > 256) {
					throw new InvalidArgumentException('The text of the query is more length of 256 characters.');
				}

				$this -> query = $value;
			case 'offset':
				$this -> offset = $value;
		}
	}

	/**
	 * @internal Return a string version of the object.
	 *
	 * @uses InlineQuery::__debugInfo to retrieve an array version of the class.
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
