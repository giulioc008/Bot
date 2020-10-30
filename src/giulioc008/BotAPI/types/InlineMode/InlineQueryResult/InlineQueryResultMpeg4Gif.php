<?php
/**
 * This file contains the source code of the InlineQueryResultMpeg4Gif object.
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
 * @link https://core.telegram.org/bots/api#inlinequeryresultmpeg4mpeg4 The InlineQueryResultMpeg4Gif object.
 *
 * @package giulioc008\BotAPI\types\InlineMode\InlineQueryResult
 */
class InlineQueryResultMpeg4Gif extends InlineQueryResult {
	/**
	* @var int $id The id of the result.
	*/
	private int $id;
	/**
	* @var string $type The type of the result.
	*/
	private string $type;
	/**
	 * @var string $mpeg4_url The URL of the video of the result.
	 */
	private string $mpeg4_url;
	/**
	 * @var string $thumb_url The URL of the thumbnail of the video.
	 */
	private string $thumb_url;
	/**
	 * @var string $parse_mode The parse mode of the message.
	 */
	private string $parse_mode;
	/**
	 * @var ?int $mpeg4_width The width of the video of the result.
	 */
	private ?int $mpeg4_width;
	/**
	 * @var ?int $mpeg4_height The height of the video of the result.
	 */
	private ?int $mpeg4_height;
	/**
	 * @var ?int $mpeg4_duration The duration, expressed in seconds, of the video.
	 */
	private ?int $mpeg4_duration;
	/**
	 * @var ?string $thumb_mime_type The mime type of the thumbnail of the video.
	 */
	private ?string $thumb_mime_type;
	/**
	 * @var ?string $title The title of the video.
	 */
	private ?string $title;
	/**
	 * @var ?string $caption The caption of the video.
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
	 * @param $mpeg4_url				string					The URL of the video of the result.
	 * @param $thumb_url				string					The URL of the thumbnail of the video.
	 * @param $mpeg4_width				?int					The width of the video of the result.
	 * @param $mpeg4_height				?int					The height of the video of the result.
	 * @param $mpeg4_duration			?int					The duration, expressed in seconds, of the video.
	 * @param $thumb_mime_type			?string					The mime type of the thumbnail of the video.
	 * @param $title					?string					The title of the video.
	 * @param $caption					?string					The caption of the video.
	 * @param $parse_mode				?string					The parse mode of the message.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 * @param $input_message_content	?InputMessageContent	The content of the message to be sent instead of the audio.
	 *
	 * @throws InvalidArgumentException If the type of the result isn't 'mpeg4_gif'.
	 * @throws InvalidArgumentException If the caption of the video is more length of 1024 characters.
	 * @throws InvalidArgumentException If the mime type of the thumbnail of the result isn't a supported mime type.
	 * @throws InvalidArgumentException If the parse mode of the message isn't a supported parse mode.
	 *
	 * @return void
	 */
	public function __construct(int $id, string $type, string $mpeg4_url, string $thumb_url, ?int $mpeg4_width, ?int $mpeg4_height, ?int $mpeg4_duration, ?string $thumb_mime_type, ?string $title, ?string $caption, ?string $parse_mode, ?InlineKeyboardMarkup $reply_markup, ?InputMessageContent $input_message_content) {
		// Checking if the type of the result respect the constraints
		if ($type !== 'mpeg4_gif') {
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
		 * Check if the mime type of the thumbnail of the result isn't setted
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
		} else if (empty($thumb_mime_type)) {
			$thumb_mime_type = 'image/jpeg';
		/**
		 * Check if the mime type of the thumbnail of the result isn't a supported mime type
		 *
		 * in_array() Checks if a value exists in an array
		 */
		} else if (in_array($thumb_mime_type, [
			'image/jpeg',
			'image/gif',
			'video/mp4'
		]) === FALSE) {
			throw new InvalidArgumentException('The mime type of the thumbnail of the result isn&apos;t a supported mime type.');
		}

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
		if (empty($parse_mode)) {
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
		$this -> mpeg4_url = $mpeg4_url;
		$this -> thumb_url = $thumb_url;
		$this -> parse_mode = $parse_mode;
		$this -> mpeg4_width = $mpeg4_width;
		$this -> mpeg4_height = $mpeg4_height;
		$this -> mpeg4_duration = $mpeg4_duration;
		$this -> thumb_mime_type = $thumb_mime_type;
		$this -> title = $title;
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
			'mpeg4_url' => $this -> mpeg4_url,
			'thumb_url' => $this -> thumb_url,
			'parse_mode' => $this -> parse_mode,
			'mpeg4_width' => $this -> mpeg4_width,
			'mpeg4_height' => $this -> mpeg4_height,
			'mpeg4_duration' => $this -> mpeg4_duration,
			'thumb_mime_type' => $this -> thumb_mime_type,
			'title' => $this -> title,
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
			case 'mpeg4_url':
				return $this -> mpeg4_url;
			case 'thumb_url':
				return $this -> thumb_url;
			case 'parse_mode':
				return $this -> parse_mode;
			case 'mpeg4_width':
				return $this -> mpeg4_width;
			case 'mpeg4_height':
				return $this -> mpeg4_height;
			case 'mpeg4_duration':
				return $this -> mpeg4_duration;
			case 'thumb_mime_type':
				return $this -> thumb_mime_type;
			case 'title':
				return $this -> title;
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
	 * @uses InlineQueryResultMpeg4Gif::__construct to create the class.
	 *
	 * @param $id						int						The id of the result.
	 * @param $type						string					The type of the result.
	 * @param $mpeg4_url					string					The URL of the video of the result.
	 * @param $thumb_url				string					The URL of the thumbnail of the video.
	 * @param $mpeg4_width				?int					The width of the video of the result.
	 * @param $mpeg4_height				?int					The height of the video of the result.
	 * @param $mpeg4_duration				?int					The duration, expressed in seconds, of the video.
	 * @param $thumb_mime_type			?string					The mime type of the thumbnail of the video.
	 * @param $title					?string					The title of the video.
	 * @param $caption					?string					The caption of the video.
	 * @param $parse_mode				?string					The parse mode of the message.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 * @param $input_message_content	?InputMessageContent	The content of the message to be sent instead of the audio.
	 *
	 * @throws InvalidArgumentException If the type of the result isn't 'mpeg4'.
	 * @throws InvalidArgumentException If the caption of the video is more length of 1024 characters.
	 * @throws InvalidArgumentException If the mime type of the thumbnail of the result isn't a supported mime type.
	 * @throws InvalidArgumentException If the parse mode of the message isn't a supported parse mode.
	 *
	 * @return mixed
	 */
	public function __invoke(int $id, string $type, string $mpeg4_url, string $thumb_url, ?int $mpeg4_width, ?int $mpeg4_height, ?int $mpeg4_duration, ?string $thumb_mime_type, ?string $title, ?string $caption, ?string $parse_mode, ?InlineKeyboardMarkup $reply_markup, ?InputMessageContent $input_message_content) {
		$this -> __construct($id, $type, $mpeg4_url, $thumb_url, $mpeg4_width, $mpeg4_height, $mpeg4_duration, $thumb_mime_type, $title, $caption, $parse_mode, $reply_markup, $input_message_content);
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
			case 'mpeg4_url':
				return empty($this -> mpeg4_url) === FALSE || $this -> mpeg4_url === '0';
			case 'thumb_url':
				return empty($this -> thumb_url) === FALSE || $this -> thumb_url === '0';
			case 'parse_mode':
				return empty($this -> parse_mode) === FALSE;
			case 'mpeg4_width':
				return empty($this -> mpeg4_width) === FALSE;
			case 'mpeg4_height':
				return empty($this -> mpeg4_height) === FALSE;
			case 'mpeg4_duration':
				return empty($this -> mpeg4_duration) === FALSE;
			case 'thumb_mime_type':
				return empty($this -> thumb_mime_type) === FALSE;
			case 'title':
				return empty($this -> title) === FALSE || $this -> title === '0';
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
				if ($value !== 'mpeg4_gif') {
					throw new InvalidArgumentException('The result isn&apos;t of the correct type.');
				}

				$this -> type = $value;
			case 'mpeg4_url':
				$this -> mpeg4_url = $value;
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
			case 'mpeg4_width':
				$this -> mpeg4_width = $value;
			case 'mpeg4_height':
				$this -> mpeg4_height = $value;
			case 'mpeg4_duration':
				$this -> mpeg4_duration = $value;
			case 'thumb_mime_type':
				/**
				 * Check if the mime type of the thumbnail of the result isn't setted
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
					$value = 'image/jpeg';
				/**
				 * Check if the mime type of the thumbnail of the result isn't a supported mime type
				 *
				 * in_array() Checks if a value exists in an array
				 */
				} else if (in_array($value, [
					'image/jpeg',
					'image/gif',
					'video/mp4'
				]) === FALSE) {
					throw new InvalidArgumentException('The mime type of the thumbnail of the result isn&apos;t a supported mime type.');
				}

				$this -> thumb_mime_type = $value;
			case 'title':
				$this -> title = $value;
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
