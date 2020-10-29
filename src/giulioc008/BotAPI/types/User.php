<?php
/**
 * This file contains the source code of the User object.
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

namespace giulioc008\BotAPI\types;

/**
 * @link https://core.telegram.org/bots/api#user The User object.
 *
 * @package giulioc008\BotAPI\types
 */
class User {
	/**
	 * @var int $id The id of the user.
	 */
	private int $id;
	/**
	 * @var bool $is_bot Determine if the user is a bot.
	 */
	private bool $is_bot;
	/**
	 * @var string $first_name The first name of the user.
	 */
	private string $first_name;
	/**
	 * @var ?string $last_name The last name of the user.
	 */
	private ?string $last_name;
	/**
	 * @var ?string $username The username of the user.
	 */
	private ?string $username;
	/**
	 * @var ?string $language_code The language of the user in the IETF format.
	 */
	private ?string $language_code;
	/**
	 * @var ?bool $can_join_groups Determine if the user can be invited to groups.
	 */
	private ?bool $can_join_groups;
	/**
	 * @var ?bool $can_read_all_group_messages Determine if the bot have privacy mode enabled.
	 */
	private ?bool $can_read_all_group_messages;
	/**
	 * @var ?bool $supports_inline_queries Determine if the bot supports inline queries.
	 */
	private ?bool $supports_inline_queries;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param $id							int		The id of the user.
	 * @param $is_bot						bool	Determine if the user is a bot.
	 * @param $first_name					string	The first name of the user.
	 * @param $last_name					?string The last name of the user.
	 * @param $username						?string The username of the user.
	 * @param $language_code				?string The language of the user in the IETF format.
	 * @param $can_join_groups				?bool	Determine if the user can be invited to groups.
	 * @param $can_read_all_group_messages	?bool	Determine if the bot have privacy mode enabled.
	 * @param $supports_inline_queries		?bool	Determine if the bot supports inline queries.
	 *
	 * @return void
	 */
	public function __construct(int $id, bool $is_bot, string $first_name, ?string $last_name = NULL, ?string $username = NULL, ?string $language_code = NULL, ?bool $can_join_groups = NULL, ?bool $can_read_all_group_messages = NULL, ?bool $supports_inline_queries = NULL,) {
		$this -> id = $id;
		$this -> is_bot = $is_bot;
		$this -> first_name = $first_name;
		$this -> last_name = $last_name;
		$this -> username = $username;
		$this -> language_code = $language_code;
		$this -> can_join_groups = $can_join_groups;
		$this -> can_read_all_group_messages = $can_read_all_group_messages;
		$this -> supports_inline_queries = $supports_inline_queries;
	}

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	public function __debugInfo() : array {
		return [
		'id' => $this -> id,
		'is_bot' => $this -> is_bot,
		'first_name' => $this -> first_name,
		'last_name' => $this -> last_name,
		'username' => $this -> username,
		'language_code' => $this -> language_code,
		'can_join_groups' => $this -> can_join_groups,
		'can_read_all_group_messages' => $this -> can_read_all_group_messages,
		'supports_inline_queries' => $this -> supports_inline_queries
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
			case 'is_bot':
				return $this -> is_bot;
			case 'first_name':
				return $this -> first_name;
			case 'last_name':
				return $this -> last_name;
			case 'username':
				return $this -> username;
			case 'language_code':
				return $this -> language_code;
			case 'can_join_groups':
				return $this -> can_join_groups;
			case 'can_read_all_group_messages':
				return $this -> can_read_all_group_messages;
			case 'supports_inline_queries':
				return $this -> supports_inline_queries;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses User::__construct to create the class.
	 *
	 * @param $id							int		The id of the user.
	 * @param $is_bot						bool	Determine if the user is a bot.
	 * @param $first_name					string	The first name of the user.
	 * @param $last_name					?string The last name of the user.
	 * @param $username						?string The username of the user.
	 * @param $language_code				?string The language of the user in the IETF format.
	 * @param $can_join_groups				?bool	Determine if the user can be invited to groups.
	 * @param $can_read_all_group_messages	?bool	Determine if the bot have privacy mode enabled.
	 * @param $supports_inline_queries		?bool	Determine if the bot supports inline queries.
	 *
	 * @return mixed
	 */
	public function __invoke(int $id, bool $is_bot, string $first_name, ?string $last_name = NULL, ?string $username = NULL, ?string $language_code = NULL, ?bool $can_join_groups = NULL, ?bool $can_read_all_group_messages = NULL, ?bool $supports_inline_queries = NULL,) {
		$this -> __construct($id, $is_bot, $first_name, $last_name, $username, $language_code, $can_join_groups, $can_read_all_group_messages, $supports_inline_queries);
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
			case 'is_bot':
				return empty($this -> is_bot) === FALSE;
			case 'first_name':
				return empty($this -> first_name) === FALSE || $this -> first_name === '0';
			case 'last_name':
				return empty($this -> last_name) === FALSE || $this -> last_name === '0';
			case 'username':
				return empty($this -> username) === FALSE || $this -> username === '0';
			case 'language_code':
				return empty($this -> language_code) === FALSE || $this -> language_code === '0';
			case 'can_join_groups':
				return empty($this -> can_join_groups) === FALSE;
			case 'can_read_all_group_messages':
				return empty($this -> can_read_all_group_messages) === FALSE;
			case 'supports_inline_queries':
				return empty($this -> supports_inline_queries) === FALSE;
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
			case 'is_bot':
				$this -> is_bot = $value;
			case 'first_name':
				$this -> first_name = $value;
			case 'last_name':
				$this -> last_name = $value;
			case 'username':
				$this -> username = $value;
			case 'language_code':
				$this -> language_code = $value;
			case 'can_join_groups':
				$this -> can_join_groups = $value;
			case 'can_read_all_group_messages':
				$this -> can_read_all_group_messages = $value;
			case 'supports_inline_queries':
				$this -> supports_inline_queries = $value;
		}
	}

	/**
	 * @internal Return a string version of the object.
	 *
	 * @uses User::__debugInfo to retrieve an array version of the class.
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
