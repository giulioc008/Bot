<?php
/**
 * This file contains the source code of the Update object relative to a new post in a channel.
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
use giulioc008\BotAPI\types\Message;

/**
 * @link https://core.telegram.org/bots/api#update The Update object relative to a new post in a channel.
 *
 * @package giulioc008\BotAPI\types\Update
 */
class UpdateNewChannelMessage extends Update {
	/**
	* @var int $id The update's unique identifier.
	*/
	private int $id;
	/**
	* @var Message $channel_post New incoming channel post of any kind.
	*/
	private Message $channel_post;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param $id					int			The update's unique identifier.
	 * @param $channel_post			Message		New incoming channel post of any kind.
	 *
	 * @return void
	 */
	public function __construct(int $id, Message $channel_post) {
		$this -> id = $id;
		$this -> channel_post = $channel_post;
	}

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	public function __debugInfo() : array {
		return [
			'id' => $this -> id,
			'channel_post' => $this -> channel_post
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
			case 'channel_post':
				return $this -> channel_post;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses UpdateNewChannelMessage::__construct to create the class.
	 *
	 * @param $id					int			The update's unique identifier.
	 * @param $channel_post			Message		New incoming channel post of any kind.
	 *
	 * @return mixed
	 */
	public function __invoke(int $id, Message $channel_post) {
		$this -> __construct($id, $channel_post);
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
			case 'channel_post':
				return empty($this -> channel_post) === FALSE;
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
			case 'channel_post':
				$this -> channel_post = $value;
		}
	}
}
