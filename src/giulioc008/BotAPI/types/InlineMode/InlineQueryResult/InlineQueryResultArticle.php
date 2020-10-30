<?php
/**
 * This file contains the source code of the InlineQueryResultArticle object.
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

namespace giulioc008\BotAPI\types\InlineMode\InlineQueryResult;

// Adding the necessary classes
use giulioc008\BotAPI\types\InlineMode\InlineQueryResult\InlineQueryResult;
use giulioc008\BotAPI\types\InlineMode\InputMessageContent\InputMessageContent;
use giulioc008\BotAPI\types\Keyboard\InlineKeyboardMarkup;

/**
 * @link https://core.telegram.org/bots/api#inlinequeryresultarticle The InlineQueryResultArticle object.
 *
 * @package giulioc008\BotAPI\types\InlineMode\InlineQueryResult
 */
class InlineQueryResultArticle extends InlineQueryResult {
	/**
	* @var int $id The id of the result.
	*/
	private int $id;
	/**
	* @var string $type The type of the result.
	*/
	private string $type;
	/**
	 * @var string $title The title of the result.
	 */
	private string $title;
	/**
	 * @var InputMessageContent $input_message_content The content of the message to be sent.
	 */
	private InputMessageContent $input_message_content;
	/**
	 * @var ?InlineKeyboardMarkup $reply_markup The InlineKeyboard attached to the message.
	 */
	private ?InlineKeyboardMarkup $reply_markup;
	/**
	 * @var ?string $url The URL of the result.
	 */
	private ?string $url;
	/**
	 * @var ?bool $hide_url Determine if the message must have the web preview.
	 */
	private ?bool $hide_url;
	/**
	 * @var ?string $description The description of the result.
	 */
	private ?string $description;
	/**
	 * @var ?string $thumb_url The URL of the thumbnail of the result.
	 */
	private ?string $thumb_url;
	/**
	 * @var ?int $thumb_width The width of the thumbnail of the result.
	 */
	private ?int $thumb_width;
	/**
	 * @var ?int $thumb_height The height of the thumbnail of the result.
	 */
	private ?int $thumb_height;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param $id						int						The id of the result.
	 * @param $type 					string					The type of the result.
	 * @param $title					string					The title of the result.
	 * @param $input_message_content	InputMessageContent		The content of the message to be sent.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 * @param $url						?string					The URL of the result.
	 * @param $hide_url					?bool					Determine if the message must have the web preview.
	 * @param $description				?string					The description of the result.
	 * @param $thumb_url				?string					The URL of the thumbnail of the result.
	 * @param $thumb_width				?int					The width of the thumbnail of the result.
	 * @param $thumb_height				?int					The height of the thumbnail of the result.
	 *
	 * @throws InvalidArgumentException If the type of the result isn't 'article'.
	 *
	 * @return void
	 */
	public function __construct(int $id, string $type, string $title, InputMessageContent $input_message_content, ?InlineKeyboardMarkup $reply_markup, ?string $url, ?bool $hide_url, ?string $description, ?string $thumb_url, ?int $thumb_width, ?int $thumb_height) {
		// Checking if the type of the result respect the constraints
		if ($type !== 'article') {
			throw new InvalidArgumentException('The result isn&apos;t of the correct type.');
		}

		$this -> id = $id;
		$this -> type = $type;
		$this -> title = $title;
		$this -> input_message_content = $input_message_content;
		$this -> reply_markup = $reply_markup;
		$this -> url = $url;
		$this -> hide_url = $hide_url;
		$this -> description = $description;
		$this -> thumb_url = $thumb_url;
		$this -> thumb_width = $thumb_width;
		$this -> thumb_height = $thumb_height;
	}

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	public function __debugInfo() : array {
		return [
			'id' => $this -> id,
			'type' => $this -> type,
			'title' => $this -> title,
			'input_message_content' => $this -> input_message_content,
			'reply_markup' => $this -> reply_markup,
			'url' => $this -> url,
			'hide_url' => $this -> hide_url,
			'description' => $this -> description,
			'thumb_url' => $this -> thumb_url,
			'thumb_width' => $this -> thumb_width,
			'thumb_height' => $this -> thumb_height
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
			case 'type':
				return $this -> type;
			case 'title':
				return $this -> title;
			case 'input_message_content':
				return $this -> input_message_content;
			case 'reply_markup':
				return $this -> reply_markup;
			case 'url':
				return $this -> url;
			case 'hide_url':
				return $this -> hide_url;
			case 'description':
				return $this -> description;
			case 'thumb_url':
				return $this -> thumb_url;
			case 'thumb_width':
				return $this -> thumb_width;
			case 'thumb_height':
				return $this -> thumb_height;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses InlineQueryResultArticle::__construct to create the class.
	 *
	 * @param $id	int		The id of the result.
	 * @param $type string	The type of the result.
	 * @param $title					string					The title of the result.
	 * @param $input_message_content	InputMessageContent		The content of the message to be sent.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 * @param $url						?string					The URL of the result.
	 * @param $hide_url					?bool					Determine if the message must have the web preview.
	 * @param $description				?string					The description of the result.
	 * @param $thumb_url				?string					The URL of the thumbnail of the result.
	 * @param $thumb_width				?int					The width of the thumbnail of the result.
	 * @param $thumb_height				?int					The height of the thumbnail of the result.
	 *
	 * @throws InvalidArgumentException If the type of the result isn't 'article'.
	 *
	 * @return mixed
	 */
	public function __invoke(int $id, string $type, string $title, InputMessageContent $input_message_content, ?InlineKeyboardMarkup $reply_markup, ?string $url, ?bool $hide_url, ?string $description, ?string $thumb_url, ?int $thumb_width, ?int $thumb_height) {
		$this -> __construct($id, $type, $title, $input_message_content, $reply_markup, $url, $hide_url, $description, $thumb_url, $thumb_width, $thumb_height);
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
			case 'type':
				return empty($this -> type) === FALSE || $this -> type === '0';
			case 'title':
				return empty($this -> title) === FALSE || $this -> title === '0';
			case 'input_message_content':
				return empty($this -> input_message_content) === FALSE;
			case 'reply_markup':
				return empty($this -> reply_markup) === FALSE;
			case 'url':
				return empty($this -> url) === FALSE || $this -> url === '0';
			case 'hide_url':
				return empty($this -> hide_url) === FALSE;
			case 'description':
				return empty($this -> description) === FALSE || $this -> description === '0';
			case 'thumb_url':
				return empty($this -> thumb_url) === FALSE || $this -> thumb_url === '0';
			case 'thumb_width':
				return empty($this -> thumb_width) === FALSE;
			case 'thumb_height':
				return empty($this -> thumb_height) === FALSE;
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
			case 'type':
				// Checking if the type of the result respect the constraints
				if ($value !== 'article') {
					throw new InvalidArgumentException('The result isn&apos;t of the correct type.');
				}

				$this -> type = $value;
			case 'title':
				$this -> title = $value;
			case 'input_message_content':
				$this -> input_message_content = $value;
			case 'reply_markup':
				$this -> reply_markup = $value;
			case 'url':
				$this -> url = $value;
			case 'hide_url':
				$this -> hide_url = $value;
			case 'description':
				$this -> description = $value;
			case 'thumb_url':
				$this -> thumb_url = $value;
			case 'thumb_width':
				$this -> thumb_width = $value;
			case 'thumb_height':
				$this -> thumb_height = $value;
		}
	}
}
