<?php
/**
 * This file contains the source code of the InlineQueryResultVoice object.
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
 * @link https://core.telegram.org/bots/api#inlinequeryresultvoice The InlineQueryResultVoice object.
 *
 * @package giulioc008\BotAPI\types\InlineMode\InlineQueryResult
 */
class InlineQueryResultVoice extends InlineQueryResult {
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
	 * @var string $voice_url The URL of the voice file.
	 */
	private string $voice_url;
	/**
	 * @var string $parse_mode The parse mode of the message.
	 */
	private string $parse_mode;
	/**
	 * @var ?InputMessageContent $input_message_content The content of the message to be sent instead of the voice.
	 */
	private ?InputMessageContent $input_message_content;
	/**
	 * @var ?InlineKeyboardMarkup $reply_markup The InlineKeyboard attached to the message.
	 */
	private ?InlineKeyboardMarkup $reply_markup;
	/**
	 * @var ?string $caption The caption of the result.
	 */
	private ?string $caption;
	/**
	 * @var ?int $voice_duration The duration, expressed in seconds, of the voice file.
	 */
	private ?int $voice_duration;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param $id						int						The id of the result.
	 * @param $type						string					The type of the result.
	 * @param $title					string					The title of the result.
	 * @param $voice_url				string					The URL of the voice file.
	 * @param $input_message_content	?InputMessageContent	The content of the message to be sent instead of the voice.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 * @param $caption					?string					The caption of the result.
	 * @param $parse_mode				?string					The parse mode of the message.
	 * @param $voice_duration			?int					The duration, expressed in seconds, of the voice file.
	 *
	 * @throws InvalidArgumentException If the type of the result isn't 'voice'.
	 * @throws InvalidArgumentException If the caption of the result is more length of 1024 characters.
	 * @throws InvalidArgumentException If the parse mode of the message isn't a supported parse mode.
	 *
	 * @return void
	 */
	public function __construct(int $id, string $type, string $title, string $voice_url, ?InputMessageContent $input_message_content, ?InlineKeyboardMarkup $reply_markup, ?string $caption, ?string $parse_mode, ?int $voice_duration) {
		// Checking if the type of the result respect the constraints
		if ($type !== 'voice') {
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

		$this -> id = $id;
		$this -> type = $type;
		$this -> title = $title;
		$this -> voice_url = $voice_url;
		$this -> parse_mode = $parse_mode;
		$this -> input_message_content = $input_message_content;
		$this -> reply_markup = $reply_markup;
		$this -> caption = $caption;
		$this -> voice_duration = $voice_duration;
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
			'voice_url' => $this -> voice_url,
			'parse_mode' => $this -> parse_mode,
			'input_message_content' => $this -> input_message_content,
			'reply_markup' => $this -> reply_markup,
			'caption' => $this -> caption,
			'voice_duration' => $this -> voice_duration
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
			case 'voice_url':
				return $this -> voice_url;
			case 'parse_mode':
				return $this -> parse_mode;
			case 'input_message_content':
				return $this -> input_message_content;
			case 'reply_markup':
				return $this -> reply_markup;
			case 'caption':
				return $this -> caption;
			case 'voice_duration':
				return $this -> voice_duration;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses InlineQueryResultVoice::__construct to create the class.
	 *
	 * @param $id	int		The id of the result.
	 * @param $type string	The type of the result.
	 * @param $title					string					The title of the result.
	 * @param $voice_url				string					The URL of the voice file.
	 * @param $input_message_content	?InputMessageContent	The content of the message to be sent instead of the voice.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 * @param $caption					?string					The caption of the result.
	 * @param $parse_mode				?string					The parse mode of the message.
	 * @param $voice_duration			?int					The duration, expressed in seconds, of the voice file.
	 *
	 * @throws InvalidArgumentException If the type of the result isn't 'voice'.
	 * @throws InvalidArgumentException If the caption of the result is more length of 1024 characters.
	 * @throws InvalidArgumentException If the parse mode of the message isn't a supported parse mode.
	 *
	 * @return mixed
	 */
	public function __invoke(int $id, string $type, string $title, string $voice_url, ?InputMessageContent $input_message_content, ?InlineKeyboardMarkup $reply_markup, ?string $caption, ?string $parse_mode, ?int $voice_duration) {
		$this -> __construct($id, $type, $title, $voice_url, $input_message_content, $reply_markup, $caption, $parse_mode, $voice_duration);
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
			case 'voice_url':
				return empty($this -> voice_url) === FALSE || $this -> voice_url === '0';
			case 'parse_mode':
				return empty($this -> parse_mode) === FALSE;
			case 'input_message_content':
				return empty($this -> input_message_content) === FALSE;
			case 'reply_markup':
				return empty($this -> reply_markup) === FALSE;
			case 'caption':
				return empty($this -> caption) === FALSE || $this -> caption === '0';
			case 'voice_duration':
				return empty($this -> voice_duration) === FALSE;
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
				if ($value !== 'voice') {
					throw new InvalidArgumentException('The result isn&apos;t of the correct type.');
				}

				$this -> type = $value;
			case 'title':
				$this -> title = $value;
			case 'voice_url':
				$this -> voice_url = $value;
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
			case 'input_message_content':
				$this -> input_message_content = $value;
			case 'reply_markup':
				$this -> reply_markup = $value;
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
			case 'voice_duration':
				$this -> voice_duration = $value;
		}
	}
}
