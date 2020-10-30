<?php
/**
 * This file contains the source code of the InlineQueryResultDocument object.
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
 * @link https://core.telegram.org/bots/api#inlinequeryresultdocument The InlineQueryResultDocument object.
 *
 * @package giulioc008\BotAPI\types\InlineMode\InlineQueryResult
 */
class InlineQueryResultDocument extends InlineQueryResult {
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
	 * @var string $document_url The URL of the document file.
	 */
	private string $document_url;
	/**
	 * @var string $mime_type The mime type of the content of the file.
	 */
	private string $mime_type;
	/**
	 * @var ?string $parse_mode The parse mode of the message.
	 */
	private string $parse_mode;
	/**
	 * @var ?string $caption The caption of the result.
	 */
	private ?string $caption;
	/**
	 * @var ?string $description The description of the result.
	 */
	private ?string $description;
	/**
	 * @var ?InlineKeyboardMarkup $reply_markup The InlineKeyboard attached to the message.
	 */
	private ?InlineKeyboardMarkup $reply_markup;
	/**
	 * @var ?InputMessageContent $input_message_content The content of the message to be sent instead of the audio.
	 */
	private ?InputMessageContent $input_message_content;
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
	 * @param $id						int						The result's unique identifier.
	 * @param $type						string					The type of the result.
	 * @param $title					string					The title of the result.
	 * @param $document_url				string					The URL of the document file.
	 * @param $mime_type				string					The mime type of the content of the file.
	 * @param $caption					?string					The caption of the result.
	 * @param $parse_mode				?string					The parse mode of the message.
	 * @param $description				?string					The description of the result.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 * @param $input_message_content	?InputMessageContent	The content of the message to be sent instead of the audio.
	 * @param $thumb_url				?string					The URL of the thumbnail of the result.
	 * @param $thumb_width				?int					The width of the thumbnail of the result.
	 * @param $thumb_height				?int					The height of the thumbnail of the result.
	 *
	 * @throws InvalidArgumentException If the type of the result isn't 'document'.
	 * @throws InvalidArgumentException If the caption of the result is more length of 1024 characters.
	 * @throws InvalidArgumentException If the parse mode of the message isn't a supported parse mode.
	 * @throws InvalidArgumentException If the mime type of the result isn't a supported mime type.
	 *
	 * @return void
	 */
	public function __construct(int $id, string $type, string $title, string $document_url, string $mime_type, ?string $caption, ?string $parse_mode, ?string $description, ?InlineKeyboardMarkup $reply_markup, ?InputMessageContent $input_message_content, ?string $thumb_url, ?int $thumb_width, ?int $thumb_height) {
		// Checking if the type of the result respect the constraints
		if ($type !== 'document') {
			throw new InvalidArgumentException('The result isn&apos;t of the correct type.');
		/**
		 * Checking if the caption of the result respect the constraints
		 *
		 * strlen() return the length of the string
		 */
		} else if (strlen($caption) > 1024) {
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

		/**
		 * Check if the mime type of the result isn't a supported mime type
		 *
		 * in_array() Checks if a value exists in an array
		 */
		if (in_array($mime_type, [
			'application/pdf',
			'application/zip'
		]) === FALSE) {
			throw new InvalidArgumentException('The mime type of the result isn&apos;t a supported mime type.');
		}

		$this -> id = $id;
		$this -> type = $type;
		$this -> title = $title;
		$this -> document_url = $document_url;
		$this -> mime_type = $mime_type;
		$this -> parse_mode = $parse_mode;
		$this -> caption = $caption;
		$this -> description = $description;
		$this -> reply_markup = $reply_markup;
		$this -> input_message_content = $input_message_content;
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
			'document_url' => $this -> document_url,
			'mime_type' => $this -> mime_type,
			'parse_mode' => $this -> parse_mode,
			'caption' => $this -> caption,
			'description' => $this -> description,
			'reply_markup' => $this -> reply_markup,
			'input_message_content' => $this -> input_message_content,
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
			case 'document_url':
				return $this -> document_url;
			case 'mime_type':
				return $this -> mime_type;
			case 'parse_mode':
				return $this -> parse_mode;
			case 'caption':
				return $this -> caption;
			case 'description':
				return $this -> description;
			case 'reply_markup':
				return $this -> reply_markup;
			case 'input_message_content':
				return $this -> input_message_content;
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
	 * @uses InlineQueryResultDocument::__construct to create the class.
	 *
	 * @param $id						int						The result's unique identifier.
	 * @param $type						string					The type of the result.
	 * @param $title					string					The title of the result.
	 * @param $document_url				string					The URL of the audio file.
	 * @param $mime_type				string					The mime type of the content of the file.
	 * @param $caption					?string					The caption of the result.
	 * @param $parse_mode				?string					The parse mode of the message.
	 * @param $description				?string					The description of the result.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 * @param $input_message_content	?InputMessageContent	The content of the message to be sent instead of the audio.
	 * @param $thumb_url				?string					The URL of the thumbnail of the result.
	 * @param $thumb_width				?int					The width of the thumbnail of the result.
	 * @param $thumb_height				?int					The height of the thumbnail of the result.
	 *
	 * @throws InvalidArgumentException If the type of the result isn't 'document'.
	 * @throws InvalidArgumentException If the caption of the result is more length of 1024 characters.
	 * @throws InvalidArgumentException If the parse mode of the message isn't a supported parse mode.
	 * @throws InvalidArgumentException If the mime type of the result isn't a supported mime type.
	 *
	 * @return mixed
	 */
	public function __invoke(int $id, string $type, string $title, string $document_url, string $document_url, ?string $caption, ?string $parse_mode, ?string $description, ?InlineKeyboardMarkup $reply_markup, ?InputMessageContent $input_message_content, ?string $thumb_url, ?int $thumb_width, ?int $thumb_height) {
		$this -> __construct($id, $type, $title, $document_url, $document_url, $caption, $parse_mode, $description, $reply_markup, $input_message_content, $thumb_url, $thumb_width, $thumb_height);
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
			case 'document_url':
				return empty($this -> document_url) === FALSE || $this -> document_url === '0';
			case 'mime_type':
				return empty($this -> mime_type) === FALSE;
			case 'parse_mode':
				return empty($this -> parse_mode) === FALSE;
			case 'caption':
				return empty($this -> caption) === FALSE || $this -> caption === '0';
			case 'description':
				return empty($this -> description) === FALSE || $this -> description === '0';
			case 'reply_markup':
				return empty($this -> reply_markup) === FALSE;
			case 'input_message_content':
				return empty($this -> input_message_content) === FALSE;
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
				if ($value !== 'document') {
					throw new InvalidArgumentException('The result isn&apos;t of the correct type.');
				}

				$this -> type = $value;
			case 'title':
				$this -> title = $value;
			case 'document_url':
				$this -> document_url = $value;
			case 'mime_type':
				/**
				 * Check if the mime type of the result isn't a supported mime type
				 *
				 * in_array() Checks if a value exists in an array
				 */
				if (in_array($value, [
					'application/pdf',
					'application/zip'
				]) === FALSE) {
					throw new InvalidArgumentException('The mime type of the result isn&apos;t a supported mime type.');
				}

				$this -> mime_type = $value;
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
			case 'caption':
				/**
				 * Checking if the caption of the result respect the constraints
				 *
				 * strlen() return the length of the string
				 */
				if (strlen($value) > 1024) {
					throw new InvalidArgumentException('The caption of the result is more length of 1024 characters.');
				}

				$this -> caption = $value;
			case 'description':
				$this -> description = $value;
			case 'reply_markup':
				$this -> reply_markup = $value;
			case 'input_message_content':
				$this -> input_message_content = $value;
			case 'thumb_url':
				$this -> thumb_url = $value;
			case 'thumb_width':
				$this -> thumb_width = $value;
			case 'thumb_height':
				$this -> thumb_height = $value;
		}
	}
}
