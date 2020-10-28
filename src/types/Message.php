<?php
/**
 * This file contains the source code of the Message object.
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
require_once('Chat/Chat.php');
require_once('Media/Animation.php');
require_once('Media/Audio.php');
require_once('Media/Document.php');
require_once('Media/PhotoSize.php');
require_once('Media/Video.php');
require_once('Media/Voice.php');
require_once('User.php');

/**
 * @link https://core.telegram.org/bots/api#message The Message object.
 *
 * @package src\types
 */
class Message {
	/**
	* @var int $message_id The id of the message inside this chat.
	*/
	private int $message_id;
	/**
	* @var int $date The date, expressed in Unix time, the message was sent.
	*/
	private int $date;
	/**
	* @var Chat $chat The chat the message belongs to.
	*/
	private Chat $chat;
	/**
	* @var ?User $from The sender of the message.
	*/
	private ?User $from;
	/**
	* @var ?User $forward_from For forwarded messages, the sender of the original message.
	*/
	private ?User $forward_from;
	/**
	* @var ?Chat $forward_from_chat For messages forwarded from channels, information about the original channel.
	*/
	private ?Chat $forward_from_chat;
	/**
	* @var ?int $forward_from_message_id For messages forwarded from channels, the id of the original message in the channel.
	*/
	private ?int $forward_from_message_id;
	/**
	* @var ?string $forward_signature For messages forwarded from channels, the signature of the post author.
	*/
	private ?string $forward_signature;
	/**
	* @var ?string $forward_sender_name For forwarded messages, the name of the sender of the original message.
	*/
	private ?string $forward_sender_name;
	/**
	* @var ?int $forward_date For forwarded messages, the date, expressed in Unix time, the original message was sent.
	*/
	private ?int $forward_date;
	/**
	* @var ?Message $reply_to_message For replies, the original message.
	*/
	private ?Message $reply_to_message;
	/**
	* @var ?User $via_bot The bot through which the message was sent.
	*/
	private ?User $via_bot;
	/**
	* @var ?int $edit_date The date, expressed in Unix time, the message was last edited.
	*/
	private ?int $edit_date;
	/**
	* @var ?string $media_group_id The id of the media message group this message belongs to.
	*/
	private ?string $media_group_id;
	/**
	* @var ?string $author_signature For messages in channels, the signature of the post author.
	*/
	private ?string $author_signature;
	/**
	* @var ?string $text For text messages, the actual UTF-8 text of the message.
	*/
	private ?string $text;
	/**
	* @var ?Animation $animation For animation messages, information about the animation.
	*/
	private ?Animation $animation;
	/**
	* @var ?Audio $audio For audio messages, information about the audio file.
	*/
	private ?Audio $audio;
	/**
	* @var ?Document $document For document messages, information about the file.
	*/
	private ?Document $document;
	/**
	* @var ?PhotoSize[] $photo For photo messages, available sizes of the photo.
	*/
	private ?array $photo;
	/**
	* @var ?Video $video For video messages, information about the video.
	*/
	private ?Video $video;
	/**
	* @var ?Voice $voice For voice messages, information about the voice file.
	*/
	private ?Voice $voice;
	/**
	* @var ?string $caption For media messages, the caption of the message.
	*/
	private ?string $caption;
	/**
	* @var ?User[] $new_chat_members Service message: the array with the new members that were added to the chat.
	*/
	private ?array $new_chat_members;
	/**
	* @var ?User $left_chat_member Service message: the member that was removed from the chat.
	*/
	private ?User $left_chat_member;
	/**
	* @var ?string $new_chat_title Service message: the new title of the chat.
	*/
	private ?string $new_chat_title;
	/**
	* @var ?PhotoSize[] $new_chat_photo Service message: the new photo of the chat.
	*/
	private ?array $new_chat_photo;
	/**
	* @var ?bool $delete_chat_photo Service message: the chat photo was deleted.
	*/
	private ?bool $delete_chat_photo;
	/**
	* @var ?bool $group_chat_created Service message: the chat has been created.
	*/
	private ?bool $group_chat_created;
	/**
	* @var ?int $migrate_to_chat_id Service message: the new id of the chat.
	*/
	private ?int $migrate_to_chat_id;
	/**
	* @var ?int $migrate_from_chat_id Service message: the old id of the chat.
	*/
	private ?int $migrate_from_chat_id;
	/**
	* @var ?Message $pinned_message Service message: the pinned message.
	*/
	private ?Message $pinned_message;
	/**
	* @var ?InlineKeyboardMarkup $reply_markup The InlineKeyboard attached to the message.
	*/
	private ?InlineKeyboardMarkup $reply_markup;

	/**
	 * @internal The constructor of the class.
	 *
	 * @param $message_id 				int 					The id of the message inside this chat.
	 * @param $date 					int 					The date, expressed in Unix time, the message was sent.
	 * @param $chat 					Chat 					The chat the message belongs to.
	 * @param $from 					?User 					The sender of the message.
	 * @param $forward_from 			?User 					For forwarded messages, the sender of the original message.
	 * @param $forward_from_chat 		?Chat 					For messages forwarded from channels, information about the original channel.
	 * @param $forward_from_message_id 	?int 					For messages forwarded from channels, the id of the original message in the channel.
	 * @param $forward_signature 		?string 				For messages forwarded from channels, the signature of the post author.
	 * @param $forward_sender_name 		?string 				For forwarded messages, the name of the sender of the original message.
	 * @param $forward_date 			?int 					For forwarded messages, the date, expressed in Unix time, the original message was sent.
	 * @param $reply_to_message 		?Message 				For replies, the original message.
	 * @param $via_bot 					?User 					The bot through which the message was sent.
	 * @param $edit_date 				?int 					The date, expressed in Unix time, the message was last edited.
	 * @param $media_group_id 			?string 				The id of the media message group this message belongs to.
	 * @param $author_signature 		?string 				For messages in channels, the signature of the post author.
	 * @param $text 					?string 				For text messages, the actual UTF-8 text of the message.
	 * @param $animation 				?Animation 				For animation messages, information about the animation.
	 * @param $audio 					?Audio 					For audio messages, information about the audio file.
	 * @param $document 				?Document 				For document messages, information about the file.
	 * @param $photo 					?PhotoSize[]			For photo messages, available sizes of the photo.
	 * @param $video 					?Video 					For video messages, information about the video.
	 * @param $voice 					?Voice 					For voice messages, information about the voice file.
	 * @param $caption 					?string 				For media messages, the caption of the message.
	 * @param $new_chat_members	 		?User[] 				Service message: the array with the new members that were added to the chat.
	 * @param $left_chat_member 		?User 					Service message: the member that was removed from the chat.
	 * @param $new_chat_title 			?string 				Service message: the new title of the chat.
	 * @param $new_chat_photo 			?PhotoSize[]			Service message: the new photo of the chat.
	 * @param $delete_chat_photo 		?bool 					Service message: the chat photo was deleted.
	 * @param $group_chat_created 		?bool 					Service message: the chat has been created.
	 * @param $migrate_to_chat_id 		?int 					Service message: the new id of the chat.
	 * @param $migrate_from_chat_id 	?int 					Service message: the old id of the chat.
	 * @param $pinned_message 			?Message 				Service message: the pinned message.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 *
	 * @throws InvalidArgumentException If the $text parameter is more length of 4096 characters.
	 * @throws InvalidArgumentException If the $caption parameter is more length of 1024 characters.
	 *
	 * @return void
	 */
	public function __construct(int $message_id, int $date, Chat $chat, ?User $from = NULL, ?User $forward_from = NULL, ?Chat $forward_from_chat = NULL, ?int $forward_from_message_id = NULL, ?string $forward_signature = NULL, ?string $forward_sender_name = NULL, ?int $forward_date = NULL, ?Message $reply_to_message = NULL, ?User $via_bot = NULL, ?int $edit_date = NULL, ?string $media_group_id = NULL, ?string $author_signature = NULL, ?string $text = NULL, ?Animation $animation = NULL, ?Audio $audio = NULL, ?Document $document = NULL, ?array $photo = NULL, ?Video $video = NULL, ?Voice $voice = NULL, ?string $caption = NULL, ?array $new_chat_members = NULL, ?User $left_chat_member = NULL, ?string $new_chat_title = NULL, ?array $new_chat_photo = NULL, ?bool $delete_chat_photo = NULL, ?bool $group_chat_created = NULL, ?int $migrate_to_chat_id = NULL, ?int $migrate_from_chat_id = NULL, ?Message $pinned_message = NULL, ?InlineKeyboardMarkup $reply_markup = NULL) {
		/**
		 * Checking if the $text parameter respect the constraints
		 *
		 * strlen() return the length of the string
		 */
		if (strlen($text) > 4096) {
			throw new InvalidArgumentException('The $text parameter is more length of 4096 characters.');
		}

		/**
		 * Checking if the $caption parameter respect the constraints
		 *
		 * strlen() return the length of the string
		 */
		if (strlen($caption) > 1024) {
			throw new InvalidArgumentException('The $caption parameter is more length of 1024 characters.');
		}

		$this -> message_id = $message_id;
		$this -> date = $date;
		$this -> chat = $chat;
		$this -> from = $from;
		$this -> forward_from = $forward_from;
		$this -> forward_from_chat = $forward_from_chat;
		$this -> forward_from_message_id = $forward_from_message_id;
		$this -> forward_signature = $forward_signature;
		$this -> forward_sender_name = $forward_sender_name;
		$this -> forward_date = $forward_date;
		$this -> reply_to_message = $reply_to_message;
		$this -> via_bot = $via_bot;
		$this -> edit_date = $edit_date;
		$this -> media_group_id = $media_group_id;
		$this -> author_signature = $author_signature;
		$this -> text = $text;
		$this -> animation = $animation;
		$this -> audio = $audio;
		$this -> document = $document;
		$this -> photo = $photo;
		$this -> video = $video;
		$this -> voice = $voice;
		$this -> caption = $caption;
		$this -> new_chat_members = $new_chat_members;
		$this -> left_chat_member = $left_chat_member;
		$this -> new_chat_title = $new_chat_title;
		$this -> new_chat_photo = $new_chat_photo;
		$this -> delete_chat_photo = $delete_chat_photo;
		$this -> group_chat_created = $group_chat_created;
		$this -> migrate_to_chat_id = $migrate_to_chat_id;
		$this -> migrate_from_chat_id = $migrate_from_chat_id;
		$this -> pinned_message = $pinned_message;
		$this -> reply_markup = $reply_markup;
	}

	/**
	 * @internal Return an array version of the object.
	 *
	 * @return array
	 */
	public function __debugInfo() : array {
		return [
			'message_id' => $this -> message_id,
			'date' => $this -> date,
			'chat' => $this -> chat,
			'from' => $this -> from,
			'forward_from' => $this -> forward_from,
			'forward_from_chat' => $this -> forward_from_chat,
			'forward_from_message_id' => $this -> forward_from_message_id,
			'forward_signature' => $this -> forward_signature,
			'forward_sender_name' => $this -> forward_sender_name,
			'forward_date' => $this -> forward_date,
			'reply_to_message' => $this -> reply_to_message,
			'via_bot' => $this -> via_bot,
			'edit_date' => $this -> edit_date,
			'media_group_id' => $this -> media_group_id,
			'author_signature' => $this -> author_signature,
			'text' => $this -> text,
			'animation' => $this -> animation,
			'audio' => $this -> audio,
			'document' => $this -> document,
			'photo' => $this -> photo,
			'video' => $this -> video,
			'voice' => $this -> voice,
			'caption' => $this -> caption,
			'new_chat_members' => $this -> new_chat_members,
			'left_chat_member' => $this -> left_chat_member,
			'new_chat_title' => $this -> new_chat_title,
			'new_chat_photo' => $this -> new_chat_photo,
			'delete_chat_photo' => $this -> delete_chat_photo,
			'group_chat_created' => $this -> group_chat_created,
			'migrate_to_chat_id' => $this -> migrate_to_chat_id,
			'migrate_from_chat_id' => $this -> migrate_from_chat_id,
			'pinned_message' => $this -> pinned_message,
			'reply_markup' => $this -> reply_markup
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
			case 'message_id':
				return $this -> message_id;
			case 'date':
				return $this -> date;
			case 'chat':
				return $this -> chat;
			case 'from':
				return $this -> from;
			case 'forward_from':
				return $this -> forward_from;
			case 'forward_from_chat':
				return $this -> forward_from_chat;
			case 'forward_from_message_id':
				return $this -> forward_from_message_id;
			case 'forward_signature':
				return $this -> forward_signature;
			case 'forward_sender_name':
				return $this -> forward_sender_name;
			case 'forward_date':
				return $this -> forward_date;
			case 'reply_to_message':
				return $this -> reply_to_message;
			case 'via_bot':
				return $this -> via_bot;
			case 'edit_date':
				return $this -> edit_date;
			case 'media_group_id':
				return $this -> media_group_id;
			case 'author_signature':
				return $this -> author_signature;
			case 'text':
				return $this -> text;
			case 'animation':
				return $this -> animation;
			case 'audio':
				return $this -> audio;
			case 'document':
				return $this -> document;
			case 'photo':
				return $this -> photo;
			case 'video':
				return $this -> video;
			case 'voice':
				return $this -> voice;
			case 'caption':
				return $this -> caption;
			case 'new_chat_members':
				return $this -> new_chat_members;
			case 'left_chat_member':
				return $this -> left_chat_member;
			case 'new_chat_title':
				return $this -> new_chat_title;
			case 'new_chat_photo':
				return $this -> new_chat_photo;
			case 'delete_chat_photo':
				return $this -> delete_chat_photo;
			case 'group_chat_created':
				return $this -> group_chat_created;
			case 'migrate_to_chat_id':
				return $this -> migrate_to_chat_id;
			case 'migrate_from_chat_id':
				return $this -> migrate_from_chat_id;
			case 'pinned_message':
				return $this -> pinned_message;
			case 'reply_markup':
				return $this -> reply_markup;
		}
	}

	/**
	 * @internal The constructor of the class when is used like a function.
	 *
	 * @uses Message::__construct to create the class.
	 *
	 * @param $message_id 				int 					The id of the message inside this chat.
	 * @param $date 					int 					The date, expressed in Unix time, the message was sent.
	 * @param $chat 					Chat 					The chat the message belongs to.
	 * @param $from 					?User 					The sender of the message.
	 * @param $forward_from 			?User 					For forwarded messages, the sender of the original message.
	 * @param $forward_from_chat 		?Chat 					For messages forwarded from channels, information about the original channel.
	 * @param $forward_from_message_id 	?int 					For messages forwarded from channels, the id of the original message in the channel.
	 * @param $forward_signature 		?string 				For messages forwarded from channels, the signature of the post author.
	 * @param $forward_sender_name 		?string 				For forwarded messages, the name of the sender of the original message.
	 * @param $forward_date 			?int 					For forwarded messages, the date, expressed in Unix time, the original message was sent.
	 * @param $reply_to_message 		?Message 				For replies, the original message.
	 * @param $via_bot 					?User 					The bot through which the message was sent.
	 * @param $edit_date 				?int 					The date, expressed in Unix time, the message was last edited.
	 * @param $media_group_id 			?string 				The id of the media message group this message belongs to.
	 * @param $author_signature 		?string 				For messages in channels, the signature of the post author.
	 * @param $text 					?string 				For text messages, the actual UTF-8 text of the message.
	 * @param $animation 				?Animation 				For animation messages, information about the animation.
	 * @param $audio 					?Audio 					For audio messages, information about the audio file.
	 * @param $document 				?Document 				For document messages, information about the file.
	 * @param $photo 					?PhotoSize[]			For photo messages, available sizes of the photo.
	 * @param $video 					?Video 					For video messages, information about the video.
	 * @param $voice 					?Voice 					For voice messages, information about the voice file.
	 * @param $caption 					?string 				For media messages, the caption of the message.
	 * @param $new_chat_members	 		?User[] 				Service message: the array with the new members that were added to the chat.
	 * @param $left_chat_member 		?User 					Service message: the member that was removed from the chat.
	 * @param $new_chat_title 			?string 				Service message: the new title of the chat.
	 * @param $new_chat_photo 			?PhotoSize[]			Service message: the new photo of the chat.
	 * @param $delete_chat_photo 		?bool 					Service message: the chat photo was deleted.
	 * @param $group_chat_created 		?bool 					Service message: the chat has been created.
	 * @param $migrate_to_chat_id 		?int 					Service message: the new id of the chat.
	 * @param $migrate_from_chat_id 	?int 					Service message: the old id of the chat.
	 * @param $pinned_message 			?Message 				Service message: the pinned message.
	 * @param $reply_markup				?InlineKeyboardMarkup	The InlineKeyboard attached to the message.
	 *
	 * @throws InvalidArgumentException If the $text parameter is more length of 4096 characters.
	 * @throws InvalidArgumentException If the $caption parameter is more length of 1024 characters.
	 *
	 * @return mixed
	 */
	public function __invoke(int $message_id, int $date, Chat $chat, ?User $from = NULL, ?User $forward_from = NULL, ?Chat $forward_from_chat = NULL, ?int $forward_from_message_id = NULL, ?string $forward_signature = NULL, ?string $forward_sender_name = NULL, ?int $forward_date = NULL, ?Message $reply_to_message = NULL, ?User $via_bot = NULL, ?int $edit_date = NULL, ?string $media_group_id = NULL, ?string $author_signature = NULL, ?string $text = NULL, ?Animation $animation = NULL, ?Audio $audio = NULL, ?Document $document = NULL, ?array $photo = NULL, ?Video $video = NULL, ?Voice $voice = NULL, ?string $caption = NULL, ?array $new_chat_members = NULL, ?User $left_chat_member = NULL, ?string $new_chat_title = NULL, ?array $new_chat_photo = NULL, ?bool $delete_chat_photo = NULL, ?bool $group_chat_created = NULL, ?int $migrate_to_chat_id = NULL, ?int $migrate_from_chat_id = NULL, ?Message $pinned_message = NULL, ?InlineKeyboardMarkup $reply_markup = NULL) {
		$this -> __construct($message_id, $date, $chat, $from, $forward_from, $forward_from_chat, $forward_from_message_id, $forward_signature, $forward_sender_name, $forward_date, $reply_to_message, $via_bot, $edit_date, $media_group_id, $author_signature, $text, $animation, $audio, $document, $photo, $video, $voice, $caption, $new_chat_members, $left_chat_member, $new_chat_title, $new_chat_photo, $delete_chat_photo, $group_chat_created, $migrate_to_chat_id, $migrate_from_chat_id, $pinned_message, $reply_markup);
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
			case 'message_id':
				return empty($this -> message_id) === FALSE;
			case 'date':
				return empty($this -> date) === FALSE;
			case 'chat':
				return empty($this -> chat) === FALSE;
			case 'from':
				return empty($this -> from) === FALSE;
			case 'forward_from':
				return empty($this -> forward_from) === FALSE;
			case 'forward_from_chat':
				return empty($this -> forward_from_chat) === FALSE;
			case 'forward_from_message_id':
				return empty($this -> forward_from_message_id) === FALSE;
			case 'forward_signature':
				return empty($this -> forward_signature) === FALSE || $this -> forward_signature === '0';
			case 'forward_sender_name':
				return empty($this -> forward_sender_name) === FALSE || $this -> forward_sender_name === '0';
			case 'forward_date':
				return empty($this -> forward_date) === FALSE;
			case 'reply_to_message':
				return empty($this -> reply_to_message) === FALSE;
			case 'via_bot':
				return empty($this -> via_bot) === FALSE;
			case 'edit_date':
				return empty($this -> edit_date) === FALSE;
			case 'media_group_id':
				return empty($this -> media_group_id) === FALSE || $this -> media_group_id === '0';
			case 'author_signature':
				return empty($this -> author_signature) === FALSE || $this -> author_signature === '0';
			case 'text':
				return empty($this -> text) === FALSE || $this -> text === '0';
			case 'animation':
				return empty($this -> animation) === FALSE;
			case 'audio':
				return empty($this -> audio) === FALSE;
			case 'document':
				return empty($this -> document) === FALSE;
			case 'photo':
				return empty($this -> photo) === FALSE;
			case 'video':
				return empty($this -> video) === FALSE;
			case 'voice':
				return empty($this -> voice) === FALSE;
			case 'caption':
				return empty($this -> caption) === FALSE || $this -> caption === '0';
			case 'new_chat_members':
				return empty($this -> new_chat_members) === FALSE;
			case 'left_chat_member':
				return empty($this -> left_chat_member) === FALSE;
			case 'new_chat_title':
				return empty($this -> new_chat_title) === FALSE || $this -> new_chat_title === '0';
			case 'new_chat_photo':
				return empty($this -> new_chat_photo) === FALSE;
			case 'delete_chat_photo':
				return empty($this -> delete_chat_photo) === FALSE;
			case 'group_chat_created':
				return empty($this -> group_chat_created) === FALSE;
			case 'migrate_to_chat_id':
				return empty($this -> migrate_to_chat_id) === FALSE;
			case 'migrate_from_chat_id':
				return empty($this -> migrate_from_chat_id) === FALSE;
			case 'pinned_message':
				return empty($this -> pinned_message) === FALSE;
			case 'reply_markup':
				return empty($this -> reply_markup) === FALSE;
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
			case 'message_id':
				$this -> message_id = $value;
			case 'date':
				$this -> date = $value;
			case 'chat':
				$this -> chat = $value;
			case 'from':
				$this -> from = $value;
			case 'forward_from':
				$this -> forward_from = $value;
			case 'forward_from_chat':
				$this -> forward_from_chat = $value;
			case 'forward_from_message_id':
				$this -> forward_from_message_id = $value;
			case 'forward_signature':
				$this -> forward_signature = $value;
			case 'forward_sender_name':
				$this -> forward_sender_name = $value;
			case 'forward_date':
				$this -> forward_date = $value;
			case 'reply_to_message':
				$this -> reply_to_message = $value;
			case 'via_bot':
				$this -> via_bot = $value;
			case 'edit_date':
				$this -> edit_date = $value;
			case 'media_group_id':
				$this -> media_group_id = $value;
			case 'author_signature':
				$this -> author_signature = $value;
			case 'text':
				$this -> text = $value;
			case 'animation':
				$this -> animation = $value;
			case 'audio':
				$this -> audio = $value;
			case 'document':
				$this -> document = $value;
			case 'photo':
				$this -> photo = $value;
			case 'video':
				$this -> video = $value;
			case 'voice':
				$this -> voice = $value;
			case 'caption':
				$this -> caption = $value;
			case 'new_chat_members':
				$this -> new_chat_members = $value;
			case 'left_chat_member':
				$this -> left_chat_member = $value;
			case 'new_chat_title':
				$this -> new_chat_title = $value;
			case 'new_chat_photo':
				$this -> new_chat_photo = $value;
			case 'delete_chat_photo':
				$this -> delete_chat_photo = $value;
			case 'group_chat_created':
				$this -> group_chat_created = $value;
			case 'migrate_to_chat_id':
				$this -> migrate_to_chat_id = $value;
			case 'migrate_from_chat_id':
				$this -> migrate_from_chat_id = $value;
			case 'pinned_message':
				$this -> pinned_message = $value;
			case 'reply_markup':
				$this -> reply_markup = $value;
		}
	}

	/**
	 * @internal Return a string version of the object.
	 *
	 * @uses Message::__debugInfo to create the class.
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

