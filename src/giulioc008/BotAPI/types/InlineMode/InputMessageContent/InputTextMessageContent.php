<?php
/**
 * This file contains the source code of the InputTextMessageContent object.
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

namespace giulioc008\BotAPI\types\InlineMode\InputMessageContent;

// Adding the necessary classes
use giulioc008\BotAPI\types\InlineMode\InputMessageContent\InputMessageContent;

/**
 * @link https://core.telegram.org/bots/api#inputtextmessagecontent The InputTextMessageContent object.
 *
 * @package giulioc008\BotAPI\types\InlineMode\InputMessageContent
 */
class InputTextMessageContent extends InputMessageContent {
	/**
	* @var string $message_text The text of the message to be sent.
	*/
	private string $message_text;
	/**
	 * @var string $parse_mode The parse mode of the message.
	 */
	private string $parse_mode;
	/**
	 * @var ?bool $disable_web_page_preview Determine if the message must have the web preview.
	 */
	private ?bool $disable_web_page_preview;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param $message_text 			string					The text of the message to be sent.
	 * @param $parse_mode				?string					The parse mode of the message.
	 * @param $disable_web_page_preview	?bool					Determine if the message must have the web preview.
	 *
	 * @throws InvalidArgumentException If the text of the message is more length of 4096 characters.
	 * @throws InvalidArgumentException If the parse mode of the message isn't a supported parse mode.
	 *
	 * @return void
	 */
	public function __construct(string $message_text, ?string $parse_mode, ?bool $disable_web_page_preview) {
		/**
		 * Checking if the text of the message respect the constraints
		 *
		 * strlen() return the length of the string
		 */
		if (strlen($message_text) < 1) {
			throw new InvalidArgumentException('The text of the message is empty.');
		/**
		 * Checking if the text of the message respect the constraints
		 *
		 * strlen() return the length of the string
		 */
		} else if (strlen($message_text) > 4096) {
			throw new InvalidArgumentException('The text of the message is more length of 4096 characters.');
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

		$this -> message_text = $message_text;
		$this -> parse_mode = $parse_mode;
		$this -> disable_web_page_preview = $disable_web_page_preview;
	}

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	public function __debugInfo() : array {
		return [
			'message_text' => $this -> message_text,
			'parse_mode' => $this -> parse_mode,
			'disable_web_page_preview' => $this -> disable_web_page_preview
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
			case 'message_text':
				return $this -> message_text;
			case 'parse_mode':
				return $this -> parse_mode;
			case 'disable_web_page_preview':
				return $this -> disable_web_page_preview;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses InputTextMessageContent::__construct to create the class.
	 *
	 * @param $message_text 			string					The text of the message to be sent.
	 * @param $parse_mode				?string					The parse mode of the message.
	 * @param $disable_web_page_preview	?bool					Determine if the message must have the web preview.
	 *
	 * @throws InvalidArgumentException If the text of the message is more length of 4096 characters.
	 * @throws InvalidArgumentException If the parse mode of the message isn't a supported parse mode.
	 *
	 * @return mixed
	 */
	public function __invoke(string $message_text, ?string $parse_mode, ?bool $disable_web_page_preview) {
		$this -> __construct($message_text, $parse_mode, $disable_web_page_preview);
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
			case 'message_text':
				return empty($this -> message_text) === FALSE || $this -> message_text === '0';
			case 'parse_mode':
				return empty($this -> parse_mode) === FALSE;
			case 'disable_web_page_preview':
				return empty($this -> disable_web_page_preview) === FALSE;
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
			case 'message_text':
				/**
				 * Checking if the text of the message respect the constraints
				 *
				 * strlen() return the length of the string
				 */
				if (strlen($value) < 1) {
					throw new InvalidArgumentException('The text of the message is empty.');
				/**
				 * Checking if the text of the message respect the constraints
				 *
				 * strlen() return the length of the string
				 */
				} else if (strlen($value) > 4096) {
					throw new InvalidArgumentException('The text of the message is more length of 4096 characters.');
				}

				$this -> message_text = $value;
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
			case 'disable_web_page_preview':
				$this -> disable_web_page_preview = $value;
		}
	}
}
