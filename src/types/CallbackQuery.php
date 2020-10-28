<?php
/**
 * This file contains the source code of the CallbackQuery object.
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
require_once('Message.php');
require_once('User.php');

/**
 * @link https://core.telegram.org/bots/api#callbackquery The CallbackQuery object.
 *
 * @package src\types
 */
class CallbackQuery {
	/**
	 * @var string $id The id of the query.
	 */
	private string $id;
	/**
	 * @var User $from The sender of the query.
	 */
	private User $from;
	/**
	 * @var string $chat_instance The id of the chat where the query was sent.
	 */
	private string $chat_instance;
	/**
	 * @var ?Message $message The message of the query.
	 */
	private ?Message $message;
	/**
	 * @var ?string $inline_message_id The id of inline Message associated to the query.
	 */
	private ?string $inline_message_id;
	/**
	 * @var ?string $data The data associated with the callback button.
	 */
	private ?string $data;
	/**
	 * @var ?string $game_short_name The short name of a Game to be returned.
	 */
	private ?string $game_short_name;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param string	$id					The id of the query.
	 * @param User		$from				The sender of the query.
	 * @param string	$chat_instance		The id of the chat where the query was sent.
	 * @param ?Message	$message			The message of the query.
	 * @param ?string	$inline_message_id	The id of inline Message associated to the query.
	 * @param ?string	$data				The data associated with the callback button.
	 * @param ?string	$game_short_name	The short name of a Game to be returned.
	 *
	 * @return void
	 */
	public function __construct(string $id, User $from, string $chat_instance, ?Message $message = NULL, ?string $inline_message_id = NULL, ?string $data = NULL, ?string $game_short_name = NULL) {
		$this -> id = $id;
		$this -> from = $from;
		$this -> chat_instance = $chat_instance;
		$this -> message = $message;
		$this -> inline_message_id = $inline_message_id;
		$this -> data = $data;
		$this -> game_short_name = $game_short_name;
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
			'message' => $this -> message,
			'inline_message_id' => $this -> inline_message_id,
			'chat_instance' => $this -> chat_instance,
			'data' => $this -> data,
			'game_short_name' => $this -> game_short_name
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
			case 'message':
				return $this -> message;
			case 'inline_message_id':
				return $this -> inline_message_id;
			case 'chat_instance':
				return $this -> chat_instance;
			case 'data':
				return $this -> data;
			case 'game_short_name':
				return $this -> game_short_name;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses CallbackQuery::__construct to create the class.
	 *
	 * @param string	$id					The id of the query.
	 * @param User		$from				The sender of the query.
	 * @param string	$chat_instance		The id of the chat where the query was sent.
	 * @param ?Message	$message			The message of the query.
	 * @param ?string	$inline_message_id	The id of inline Message associated to the query.
	 * @param ?string	$data				The data associated with the callback button.
	 * @param ?string	$game_short_name	The short name of a Game to be returned.
	 *
	 * @return mixed
	 */
	public function __invoke(string $id, User $from, string $chat_instance, ?Message $message = NULL, ?string $inline_message_id = NULL, ?string $data = NULL, ?string $game_short_name = NULL) {
		$this -> __construct($id, $from, $chat_instance, $message, $inline_message_id, $data, $game_short_name);
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
			case 'message':
				return empty($this -> message) === FALSE;
			case 'inline_message_id':
				return empty($this -> inline_message_id) === FALSE || $this -> inline_message_id === '0';
			case 'chat_instance':
				return empty($this -> chat_instance) === FALSE || $this -> chat_instance === '0';
			case 'data':
				return empty($this -> data) === FALSE || $this -> data === '0';
			case 'game_short_name':
				return empty($this -> game_short_name) === FALSE || $this -> game_short_name === '0';
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
			case 'from':
				$this -> from = $value;
			case 'message':
				$this -> message = $value;
			case 'inline_message_id':
				$this -> inline_message_id = $value;
			case 'chat_instance':
				$this -> chat_instance = $value;
			case 'data':
				$this -> data = $value;
			case 'game_short_name':
				$this -> game_short_name = $value;
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
