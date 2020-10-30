<?php
/**
 * This file contains the source code of the InlineQueryResultPhoto object.
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
 * @link https://core.telegram.org/bots/api#inlinequeryresultphoto The InlineQueryResultPhoto object.
 *
 * @package giulioc008\BotAPI\types\InlineMode\InlineQueryResult
 */
class InlineQueryResultPhoto extends InlineQueryResult {
	/**
	* @var int $id The id of the result.
	*/
	private int $id;
	/**
	* @var string $type The type of the result.
	*/
	private string $type;
	/**
	 * @var string $photo_url The URL of the photo of the result.
	 */
	private string $photo_url;
	/**
	 * @var string $thumb_url The URL of the thumbnail of the photo.
	 */
	private string $thumb_url;
	/**
	 * @var string $parse_mode The parse mode of the message.
	 */
	private string $parse_mode;
	/**
	 * @var ?int $photo_width The width of the photo of the result.
	 */
	private ?int $photo_width;
	/**
	 * @var ?int $photo_height The height of the photo of the result.
	 */
	private ?int $photo_height;
	/**
	 * @var ?string $title The title of the photo.
	 */
	private ?string $title;
	/**
	 * @var ?string $description The description of the result.
	 */
	private ?string $description;
	/**
	 * @var ?string $caption The caption of the photo.
	 */
	private ?string $caption;
	/**
	 * @var ?InlineKeyboardMarkup $reply_markup The InlineKeyboard attached to the message.
	 */
	private ?InlineKeyboardMarkup $reply_markup;
	/**
	 * @var ?InputMessageContent $input_message_content The content of the message to be sent instead of the audio.
	 */
	private ?InputMessageContent $input_message_content;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param $id						int						The id of the result.
	 * @param $type						string					The type of the result.
	 * @param $photo_url				string					The URL of the photo of the result.
	 * @param $thumb_url				string					The URL of the thumbnail of the photo.
	 * @param $photo_width				?int					The width of the photo of the result.
	 * @param $photo_height				?int					The height of the photo of the result.
	 * @param $title					?string					The title of the photo.
	 * @param $description				?string					The description of the result.
	 * @param $caption					?string					The caption of the photo.
	 * @param $parse_mode				?string					The parse mode of the message.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 * @param $input_message_content	?InputMessageContent	The content of the message to be sent instead of the audio.
	 *
	 * @throws InvalidArgumentException If the type of the result isn't 'photo'.
	 * @throws InvalidArgumentException If the caption of the photo is more length of 1024 characters.
	 * @throws InvalidArgumentException If the parse mode of the message isn't a supported parse mode.
	 *
	 * @return void
	 */
	public function __construct(int $id, string $type, string $photo_url, string $thumb_url, ?int $photo_width, ?int $photo_height, ?string $title, ?string $description, ?string $caption, ?string $parse_mode, ?InlineKeyboardMarkup $reply_markup, ?InputMessageContent $input_message_content) {
		// Checking if the type of the result respect the constraints
		if ($type !== 'photo') {
			throw new InvalidArgumentException('The result isn&apos;t of the correct type.');
		/**
		 * Checking if the caption of the result respect the constraints
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
		 * strlen() return the length of the string
		 */
		} else if (empty($caption) === FALSE && strlen($caption) > 1024) {
			throw new InvalidArgumentException('The caption of the result is more length of 1024 characters.');
		/**
		 * Check if the parse mode of the message isn't setted
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
		} else if (empty($parse_mode)) {
			$parse_mode = 'HTML';
		/**
		 * Check if the parse mode of the message isn't a supported parse mode
		 *
		 * in_array() Checks if a value exists in an array
		 */
		} else if (in_array($parse_mode, [
			'HTML',
			'MarkdownV2'
		]) === FALSE) {
			throw new InvalidArgumentException('The parse mode of the message isn&apos;t a supported parse mode.');
		}

		$this -> id = $id;
		$this -> type = $type;
		$this -> photo_url = $photo_url;
		$this -> thumb_url = $thumb_url;
		$this -> parse_mode = $parse_mode;
		$this -> photo_width = $photo_width;
		$this -> photo_height = $photo_height;
		$this -> title = $title;
		$this -> description = $description;
		$this -> caption = $caption;
		$this -> reply_markup = $reply_markup;
		$this -> input_message_content = $input_message_content;
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
			'photo_url' => $this -> photo_url,
			'thumb_url' => $this -> thumb_url,
			'parse_mode' => $this -> parse_mode,
			'photo_width' => $this -> photo_width,
			'photo_height' => $this -> photo_height,
			'title' => $this -> title,
			'description' => $this -> description,
			'caption' => $this -> caption,
			'reply_markup' => $this -> reply_markup,
			'input_message_content' => $this -> input_message_content
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
			case 'photo_url':
				return $this -> photo_url;
			case 'thumb_url':
				return $this -> thumb_url;
			case 'parse_mode':
				return $this -> parse_mode;
			case 'photo_width':
				return $this -> photo_width;
			case 'photo_height':
				return $this -> photo_height;
			case 'title':
				return $this -> title;
			case 'description':
				return $this -> description;
			case 'caption':
				return $this -> caption;
			case 'reply_markup':
				return $this -> reply_markup;
			case 'input_message_content':
				return $this -> input_message_content;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses InlineQueryResultPhoto::__construct to create the class.
	 *
	 * @param $id						int						The id of the result.
	 * @param $type						string					The type of the result.
	 * @param $photo_url				string					The URL of the photo of the result.
	 * @param $thumb_url				string					The URL of the thumbnail of the photo.
	 * @param $photo_width				?int					The width of the photo of the result.
	 * @param $photo_height				?int					The height of the photo of the result.
	 * @param $title					?string					The title of the photo.
	 * @param $description				?string					The description of the result.
	 * @param $caption					?string					The caption of the photo.
	 * @param $parse_mode				?string					The parse mode of the message.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 * @param $input_message_content	?InputMessageContent	The content of the message to be sent instead of the audio.
	 *
	 * @throws InvalidArgumentException If the type of the result isn't 'photo'.
	 *
	 * @return mixed
	 */
	public function __invoke(int $id, string $type, string $photo_url, string $thumb_url, ?int $photo_width, ?int $photo_height, ?string $title, ?string $description, ?string $caption, ?string $parse_mode, ?InlineKeyboardMarkup $reply_markup, ?InputMessageContent $input_message_content) {
		$this -> __construct($id, $type, $photo_url, $thumb_url, $photo_width, $photo_height, $title, $description, $caption, $parse_mode, $reply_markup, $input_message_content);
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
			case 'photo_url':
				return empty($this -> photo_url) === FALSE || $this -> photo_url === '0';
			case 'thumb_url':
				return empty($this -> thumb_url) === FALSE || $this -> thumb_url === '0';
			case 'parse_mode':
				return empty($this -> parse_mode) === FALSE;
			case 'photo_width':
				return empty($this -> photo_width) === FALSE;
			case 'photo_height':
				return empty($this -> photo_height) === FALSE;
			case 'title':
				return empty($this -> title) === FALSE || $this -> title === '0';
			case 'description':
				return empty($this -> description) === FALSE || $this -> description === '0';
			case 'caption':
				return empty($this -> caption) === FALSE || $this -> caption === '0';
			case 'reply_markup':
				return empty($this -> reply_markup) === FALSE;
			case 'input_message_content':
				return empty($this -> input_message_content) === FALSE;
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
				if ($value !== 'photo') {
					throw new InvalidArgumentException('The result isn&apos;t of the correct type.');
				}

				$this -> type = $value;
			case 'photo_url':
				$this -> photo_url = $value;
			case 'thumb_url':
				$this -> thumb_url = $value;
			case 'parse_mode':
				/**
				 * Check if the parse mode of the message isn't setted
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
				if (empty($value)) {
					$value = 'HTML';
				/**
				 * Check if the parse mode of the message isn't a supported mime type
				 *
				 * in_array() Checks if a value exists in an array
				 */
				} else if (in_array($value, [
					'HTML',
					'MarkdownV2'
				]) === FALSE) {
					throw new InvalidArgumentException('The parse mode of the message isn&apos;t a supported parse mode.');
				}

				$this -> parse_mode = $value;
			case 'photo_width':
				$this -> photo_width = $value;
			case 'photo_height':
				$this -> photo_height = $value;
			case 'title':
				$this -> title = $value;
			case 'description':
				$this -> description = $value;
			case 'caption':
				/**
				 * Checking if the caption of the result respect the constraints
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
				 * strlen() return the length of the string
				 */
				if (empty($value) === FALSE && strlen($value) > 1024) {
					throw new InvalidArgumentException('The caption of the result is more length of 1024 characters.');
				}

				$this -> caption = $value;
			case 'reply_markup':
				$this -> reply_markup = $value;
			case 'input_message_content':
				$this -> input_message_content = $value;
		}
	}
}
