<?php
	/**
	* Template for creating a Telegram bot in PHP.
	*
	* This template can be reused in accordance with the MIT license.
	*
	* @author     Giulio Coa
	* @license    https://www.linux.it/scegli-una-licenza/licenses/mit/
	*/

	// Installing the MadelineProto library
	if (file_exists('madeline.php') == FALSE) {
		copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
	}
	require_once 'madeline.php';

	// Creating the bot
	class Bot extends danog\MadelineProto\EventHandler {
		private $blacklist;
		private $DB;

		/**
		* Execute a web request to a URL
		*
		* @param string $url The URL
		* @param bool $parameters The flag that identify if the URL is a GET request with parameters
		*
		* @return string
		*/
		private function execute_request(string $url, bool $parameters = FALSE) : string {
			// Replace the special character into the URL
			$url = str_replace("\t", '%09', $url);
			$url = str_replace("\n", '%0A%0D', $url);
			$url = str_replace(' ', '%20', $url);
			$url = str_replace('\"', '%22', $url);
			$url = str_replace('#', '%23', $url);
			$url = str_replace([
				'$',
				'\$'
			], '%24', $url);
			$url = str_replace('%', '%25', $url);
			$url = str_replace('\'', '%27', $url);
			$url = str_replace(',', '%2C', $url);
			$url = str_replace(';', '%3B', $url);
			$url = str_replace('@', '%40', $url);

			// Checking if the URL isn't a GET request with parameters
			if ($parameters == FALSE) {
				$url = str_replace('=', '%3D', $url);
				$url = str_replace('?', '%3F', $url);
			}

			// Opening the connection
			$curl = curl_init($url);

			// Setting the connection
			curl_setopt_array($curl, [
				CURLOPT_HEADER => FALSE,
				CURLOPT_RETURNTRANSFER => TRUE
			]);

			// Executing the web request
			$result = curl_exec($curl);

			// Closing the connection
			curl_close($curl);

			return $result;
		}

		/**
		* Get peer(s) where to report errors
		*
		* @return array
		*/
		public function getReportPeers() : array {
			return [
				0		// The log channel
			];
		}

		/**
		* Called on startup, can contain async calls for initialization of the bot
		*
		* @return void
		*/
		public function onStart() : void {
			$this -> blacklist = [];
			$this -> DB = new mysqli('localhost', 'username', 'password', 'name', 3306);

			// Checking if there si some connection error
			if ($this -> DB -> connect_errno) {
				$this -> logger('Failed to make a MySQL connection, because ' . $this -> DB -> connect_error, \danog\MadelineProto\Logger::FATAL_ERROR);
				exit(1);
			}

			$result = $this -> DB -> query('SELECT id FROM Blacklist;');

			// Checking if the query is failed
			if ($result == FALSE) {
				$this -> logger('Failed to make the blacklist query, because ' . $this -> DB -> error, \danog\MadelineProto\Logger::FATAL_ERROR);
				exit(1);
			}

			// Checking if the query has product a result
			if ($result -> num_rows !== 0) {
				// Cycle on the result
				$this -> blacklist = $result -> fetch_all(MYSQLI_ASSOC);
				$this -> blacklist = array_map(function ($n) {
					return $n['id'];
				}, $this -> blacklist);
			}

			$result -> free();
		}

		/**
		* Handle updates from Callback Query
		*
		* @param array $update Update
		*
		* @return Generator
		*/
		public function onUpdateCallbackQuery(array $update) : Generator {
			$callback_data = trim((string) $update['data']);

			// Retrieving the data of the user that pressed the button
			$user = yield $this -> getInfo($update['user_id']);

			// Checking if the user is a normal user
			if (isset($user['User']) == FALSE || $user['User']['_'] !== 'user') {
				return;
			}

			$user = $user['User'];

			// Retrieving the language of the user
			$language = isset($sender['lang_code']) ? $sender['lang_code'] : 'en';

			// Checking if the language is supported
			$statement = $this -> DB -> prepare('SELECT NULL FROM Languages WHERE lang_code=?;');

			// Checking if the statement have errors
			if ($statement) {
				$statement -> bind_param('s', $language);
				$result = $statement -> execute();

				// Checking if the query has product a result
				if ($result == FALSE) {
					$language = 'en';
				}

				$statement -> close();
			}

			// Setting the new keyboard
			$keyboard = [];

			yield $this -> messages -> editMessage([
				'no_webpage' => TRUE,
				'peer' => $user['id'],
				'id' => $update['msg_id'],
				'reply_markup' => $keyboard,
				'parse_mode' => 'HTML'
			]);
		}

		/**
		* Handle updates from Inline Query
		*
		* @param array $update Update
		*
		* @return Generator
		*/
		public function onUpdateInlineQuery(array $update) : Generator {
			$inline_query = trim($update['query']);
			$inline_query = htmlentities($inline_query, ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML5);
			$inline_query = mysqli_real_escape_string($this -> DB, $inline_query);

			// Retrieving the data of the user that sent the query
			$user = yield $this -> getInfo($update['user_id']);

			// Checking if the user is a normal user
			if (isset($user['User']) == FALSE || $user['User']['_'] !== 'user') {
				return;
			}

			$user = $user['User'];

			// Retrieving the language of the user
			$language = isset($sender['lang_code']) ? $sender['lang_code'] : 'en';

			// Checking if the language is supported
			$statement = $this -> DB -> prepare('SELECT NULL FROM Languages WHERE lang_code=?;');

			// Checking if the statement have errors
			if ($statement) {
				$statement -> bind_param('s', $language);
				$result = $statement -> execute();

				// Checking if the query has product a result
				if ($result == FALSE) {
					$language = 'en';
				}

				$statement -> close();
			}

			/*
			* Checking if the query isn't empty
			*
			* empty() check if the query is empty
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
			if (empty($inline_query) == FALSE && strlen($inline_query) >= 3) {
				//Setting the answer
				$answer = [];

				yield $this -> messages -> setInlineResults([
					'query_id' => $update['query_id'],
					'results' => $answer,
					'cache_time' => 1
				]);
			}
		}

		/**
		* Handle updates about edited message from supergroups and channels
		*
		* @param array $update Update
		*
		* @return Generator
		*/
		public function onUpdateEditChannelMessage(array $update) : Generator {
			return $this -> onUpdateNewMessage($update);
		}

		/**
		* Handle updates about edited message from users
		*
		* @param array $update Update
		*
		* @return Generator
		*/
		public function onUpdateEditMessage(array $update) : Generator {
			return $this -> onUpdateNewMessage($update);
		}

		/**
		* Handle updates from supergroups and channels
		*
		* @param array $update Update
		*
		* @return Generator
		*/
		public function onUpdateNewChannelMessage(array $update) : Generator {
			return $this -> onUpdateNewMessage($update);
		}

		/**
		* Handle updates from users and groups
		*
		* @param array $update Update
		*
		* @return Generator
		*/
		public function onUpdateNewMessage(array $update) : Generator {
			$message = $update['message'];

			// Checking if the message is a normal message or is an incoming message
			if ($message['_'] === 'messageEmpty' || $message['out'] ?? FALSE) {
				return;
			}

			// Checking if the message is a service message
			if ($message['_'] === 'messageService') {
				// Checking if the service message is about new members
				if ($message['action']['_'] === 'messageActionChatAddUser') {
					$banned = [
						'multiple' = TRUE
					];

					// Cycle on the list of the new member
					foreach ($message['action']['users'] as $key => $value) {
						// Downloading the user's informations from the Combot Anti-Spam API
						$result = execute_request('https://api.cas.chat/check?user_id=' . $value, TRUE);
						$result = json_decode($result, TRUE);

						// Retrieving the data of the new member
						$new_member = yield $this -> getInfo($value);

						// Checking if the user isn't a spammer, isn't a deleted account and is a normal user
						if ($result['ok'] == FALSE && isset($user['User']) && $new_member['User']['_'] === 'user' && $new_member['User']['scam'] == FALSE && $new_member['User']['deleted'] == FALSE) {
							continue;
						}

						$new_member = $new_member['User'];

						$banned []= [
							'channel' => $update['chat_id'],
							'user_id' => $value,
							'banned_rights' => [
								'_' => 'chatBannedRights',
								'view_messages' => TRUE,
								'send_messages' => TRUE,
								'send_media' => TRUE,
								'send_stickers' => TRUE,
								'send_gifs' => TRUE,
								'send_games' => TRUE,
								'send_inline' => TRUE,
								'embed_links' => TRUE,
								'send_polls' => TRUE,
								'change_info' => TRUE,
								'invite_users' => TRUE,
								'pin_messages' => TRUE,
								'until_date' => 0
							]
						];
					}

					yield $this -> channels -> editBanned($banned);
				} else if ($message['action']['_'] === 'messageActionChatJoinedByLink') {
					// Downloading the user's informations from the Combot Anti-Spam API
					$result = execute_request('https://api.cas.chat/check?user_id=' . $message['from_id'], TRUE);
					$result = json_decode($result, TRUE);

					// Retrieving the data of the new member
					$new_member = yield $this -> getInfo($message['from_id']);

					// Checking if the user is a spammer, is a deleted account or isn't a normal user
					if ($result['ok'] || isset($user['User']) || $new_member['User']['_'] !== 'user' || $new_member['User']['scam'] || $new_member['User']['deleted']) {
						$new_member = $new_member['User'];

						yield $this -> channels -> editBanned([
							'channel' => $update['chat_id'],
							'user_id' => $message['from_id'],
							'banned_rights' => [
								'_' => 'chatBannedRights',
								'view_messages' => TRUE,
								'send_messages' => TRUE,
								'send_media' => TRUE,
								'send_stickers' => TRUE,
								'send_gifs' => TRUE,
								'send_games' => TRUE,
								'send_inline' => TRUE,
								'embed_links' => TRUE,
								'send_polls' => TRUE,
								'change_info' => TRUE,
								'invite_users' => TRUE,
								'pin_messages' => TRUE,
								'until_date' => 0
							]
						]);
					}
				}

				yield $this -> channels -> deleteMessages([
					'revoke' => TRUE,
					'id' => [
						$message['id']
					]
				]);
				return;
			}

			$message['message'] = trim($message['message']);
			$message['message'] = htmlentities($message['message'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML5);
			$message['message'] = mysqli_real_escape_string($this -> DB, $message['message']);

			// Checking if the chat is a channel
			if ($message['to_id']['_'] === 'peerChannel') {
				return;
			}

			// Retrieving the data of the sender
			$sender = yield $this -> getInfo($message['from_id']);

			// Checking if the user is a normal user
			if (isset($sender['User']) == FALSE || $sender['User']['_'] !== 'user') {
				return;
			}

			$sender = $sender['User'];

			// Retrieving the language of the user
			$language = isset($sender['lang_code']) ? $sender['lang_code'] : 'en';

			// Checking if the language is supported
			$statement = $this -> DB -> prepare('SELECT NULL FROM Languages WHERE lang_code=?;');

			// Checking if the statement have errors
			if ($statement) {
				$statement -> bind_param('s', $language);
				$result = $statement -> execute();

				// Checking if the query has product a result
				if ($result == FALSE) {
					$language = 'en';
				}

				$statement -> close();
			}

			// Checking if is an @admin tag
			if (preg_match('/^\@admin([[:blank:]\n]{1}((\n|.)*))?$/miu', $message['message'], $matches)) {
				// Checking if the chat is a private chat
				if ($message['to_id']['_'] === 'peerUser') {
					return;
				}

				// Retrieving the admins list
				$chat = yield $this -> getPwrChat($message['to_id']['_'] === 'peerChat' ? $message['to_id']['chat_id'] : $message['to_id']['channel_id']);

				if ($chat['type'] != 'supergroup' && $chat['type'] == 'chat') {
					return;
				}

				$admins = array_filter($chat['participants'], function ($n) {
					return $n['role'] == 'admin' || $n['role'] == 'creator';
				});

				$admins = array_map(function ($n) {
					return $n['user'];
				}, $admins);

				// Creating the message to send to the admins
				$text = "\n<a href=\"mention:" . $sender['id'] . '\" >' . $sender['first_name'] . '</a> needs your help' . (($matches[2] ?? FALSE) ? ' for ' . $matches[2] : '') . ' into <a href=\"' . $chat['invite'] . '\" >' . $chat['title'] . '</a>.';

				$message = [
					'multiple' => true
				];

				foreach ($admins as $user) {
					$message []= [
						'no_webpage' => TRUE,
						'peer' => $user['id'],
						'message' => '<a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a>,' . $text,
						'parse_mode' => 'HTML'
					];
				}

				yield $this -> messages -> sendMessage($message);

				// Sending the report to the channel
				$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> has sent an @admin request into <a href=\"' . $chat['exported_invite'] . '\" >' . $title . '</a>.');
			// Checking if is a command
			} else if (preg_match('/^\/([[:alnum:]\@]+)[[:blank:]]?([[:alnum:]]|[^\n]+)?$/miu', $message['message'], $matches)) {
				// Retrieving the command
				$command = explode('@', $matches[1])[0];
				$args = $matches[2] ?? NULL;

				switch ($command) {
					case 'announce':
						// Checking if the command has arguments
						if (isset($args)) {
							$args = html_entity_decode($args, ENT_QUOTES | ENT_HTML5, 'UTF-8');

							// Checking if the chat is a private chat
							if ($message['to_id']['_'] === 'peerUser') {
								return;
							}

							// Checking if the language is supported
							$result = $this -> DB -> query('SELECT id FROM Admins;');

							// Checking if the query is failed or if it hasn't product a result
							if ($result == FALSE || $result -> num_rows === 0) {
								$this -> logger('Failed to make the query, because ' . $this -> DB -> error, \danog\MadelineProto\Logger::ERROR);
							}

							// Cycle on the result
							$admins = $result -> fetch_all(MYSQLI_ASSOC);
							$admins = array_map(function ($n) {
								return $n['id'];
							}, $admins);

							$result -> free();

							/**
							* Checking if is a serious use of the /announce command (command runned in the staff group) and if the user is an admin of the bot
							*
							* in_array() check if the array contains an item that match the element
							*/
							if ($message['to_id']['chat_id'] == $this -> DB['staff_group'] && in_array($sender['id'], $admins)) {
								$chats = yield $this -> getDialogs();

								// Cycle on the chats where the bot is present
								foreach ($chats as $peer) {
									yield $this -> messages -> sendMessage([
										'no_webpage' => TRUE,
										'peer' => $peer,
										'message' => $args,
										'parse_mode' => 'HTML'
									]);
								}
								return;
							}

							// Retrieving the data of the chat
							$chat = yield $this -> getPwrChat($message['to_id']['chat_id']);

							// Checking if the chat is a group or a supergroup
							if ($chat['type'] != 'supergroup' && $chat['type'] != 'chat') {
								return;
							}

							/**
							* Retrieving the admins list
							*
							* array_filter() filters the array by the role of each member
							* array_map() convert each admins to its id
							*/
							$admins = array_filter($chat['participants'], function ($n) {
								return $n['role'] == 'admin' || $n['role'] == 'creator';
							});
							$admins = array_map(function ($n) {
								return $n['user']['id'];
							}, $admins);

							/**
							* Checking if the user is an admin and if the command has arguments
							*
							* in_array() check if the array contains an item that match the element
							*/
							if (in_array($sender['id'], $admins)) {
								yield $this -> messages -> sendMessage([
									'no_webpage' => TRUE,
									'peer' => $message['to_id']['chat_id'],
									'message' => $args,
									'parse_mode' => 'HTML'
								]);
							}
						}

						yield $this -> channels -> deleteMessages([
							'revoke' => TRUE,
							'id' => [
								$message['id']
							]
						]);
						break;
					case 'ban':
					case 'kick':
					case 'mute':
					case 'unban':
					case 'unmute':
						// Checking if the chat is a private chat
						if ($message['to_id']['_'] === 'peerUser') {
							return;
						}

						/**
						* Checking if is a global use of the /(un)ban command (command runned in the staff group) and if the user is an admin of the bot
						*
						* in_array() check if the array contains an item that match the element
						*/
						if (preg_match('/^(un)?ban/miu', $command) && $message['to_id']['chat_id'] == $this -> DB['staff_group'] && in_array($sender['id'], $this -> DB['admins'])) {
							// Checking if the command has arguments
							if (isset($args) == FALSE) {
								yield $this -> messages -> sendMessage([
									'no_webpage' => TRUE,
									'peer' => $message['to_id']['chat_id'],
									'message' => 'The syntax of the command is: <code>/' . $command . ' &lt;user_id|username&gt;</code>.',
									'reply_to_msg_id' => $message['id'],
									'parse_mode' => 'HTML'
								]);
								return;
							}

							// Retrieving the data of the user
							$user = yield $this -> getInfo($args);
							$user = $user['User'] ?? NULL;

							/*
							* Checking if the User is setted
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
							if (empty($user) || $user['_'] !== 'user') {
								yield $this -> messages -> sendMessage([
									'no_webpage' => TRUE,
									'peer' => $message['to_id']['chat_id'],
									'message' => 'The username/id is invalid.',
									'reply_to_msg_id' => $message['id'],
									'parse_mode' => 'HTML'
								]);
								return;
							}

							$chats = yield $this -> getDialogs();

							// Cycle on the chats where the bot is present
							foreach ($chats as $peer) {
								// Retrieving the data of the chat
								$chat = yield $this -> getInfo($peer);
								$chat = $chat['Chat'] ?? NULL;

								/*
								* Checking if the chat is setted
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
								* 	[]s
								* 	array()
								*/
								if (empty($chat) || ($chat['_'] !== 'chat' && $chat['_'] !== 'channel')) {
									continue;
								}

								yield $this -> channels -> editBanned([
									'channel' => $chat['id'],
									'user_id' => $user['id'],
									'banned_rights' => $command == 'unban' ? $chat['default_banned_rights'] : [
										'_' => 'chatBannedRights',
										'view_messages' => TRUE,
										'send_messages' => TRUE,
										'send_media' => TRUE,
										'send_stickers' => TRUE,
										'send_gifs' => TRUE,
										'send_games' => TRUE,
										'send_inline' => TRUE,
										'embed_links' => TRUE,
										'send_polls' => TRUE,
										'change_info' => TRUE,
										'invite_users' => TRUE,
										'pin_messages' => TRUE,
										'until_date' => 0
									]
								]);
							}

							// Sending the report to the channel
							$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . 'ned <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a> from all chats.');
							return;
						}

						// Setting limit to forever
						$limit = 0;

						// Checking if the command is /mute, if it has arguments and if the arguments are correct
						if ($command == 'mute' && isset($args) && preg_match('/^([[:digit:]]+)[[:blank:]]?([[:alpha:]]+)$/miu', html_entity_decode($args, ENT_QUOTES | ENT_HTML5, 'UTF-8'), $matches)) {
							$limit = $matches[1];

							// Converting the units to seconds
							switch ($matches[2]) {
								case 'm':
								case 'min':
								case 'minuto':
								case 'minute':
								case 'minuti':
								case 'minutes':
									$limit *= 60;
									break;
								case 'h':
								case 'ora':
								case 'hour':
								case 'ore':
								case 'hours':
									$limit *= 60 * 60;
									break;
								case 'g':
								case 'd':
								case 'giorno':
								case 'day':
								case 'giorni':
								case 'days':
									$limit *= 60 * 60 * 24;
									break;
								case 'M':
								case 'mese':
								case 'month':
								case 'mesi':
								case 'months':
								case 'a':
								case 'y':
								case 'anno':
								case 'year':
									$limit *= 60 * 60 * 24 * 12;
									break;
								default:
									yield $this -> messages -> sendMessage([
										'no_webpage' => TRUE,
										'peer' => $message['to_id']['chat_id'],
										'message' => "The syntax of the command is: <code>/mute [time]</code>.\nThe <code>time</code> option must be more then 30 seconds and less of 366 days.",
										'reply_to_msg_id' => $message['id'],
										'parse_mode' => 'HTML'
									]);
									break;
							}
						}

						// Retrieving the data of the chat
						$chat = yield $this -> getInfo($message['to_id']['chat_id']);
						$chat = $chat['Chat'] ?? NULL;

						/*
						* Checking if the chat is setted
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
						if (empty($chat) || ($chat['_'] !== 'chat' && $chat['_'] !== 'channel')) {
							return;
						}

						// Retrieving the message this message replies to
						$reply_message = yield $this -> messages -> getMessages([
							'id' => [
								$message['reply_to_msg_id']
							]
						]);

						// Checking if the result is valid
						if ($reply_message['_'] === 'messages.messagesNotModified') {
							return;
						}

						$reply_message = $reply_message['messages'][0];

						// Retrieving the data of the user
						$user = yield $this -> getInfo($reply_message['from_id']);
						$user = $user['User'];

						/*
						* Checking if the user is a normal user
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
						if (empty($user) || $user['_'] !== 'user') {
							return;
						}

						$user = $user['User'];

						// Checking if the command is one of: /ban, /kick or /mute
						if ($command == 'ban' || $command == 'kick' || $command == 'mute') {
							yield $this -> channels -> editBanned([
								'channel' => $message['to_id']['chat_id'],
								'user_id' => $reply_message['from_id'],
								'banned_rights' => [
									'_' => 'chatBannedRights',
									'view_messages' => $command == 'mute' ? FALSE : TRUE,
									'send_messages' => TRUE,
									'send_media' => TRUE,
									'send_stickers' => TRUE,
									'send_gifs' => TRUE,
									'send_games' => TRUE,
									'send_inline' => TRUE,
									'embed_links' => TRUE,
									'send_polls' => TRUE,
									'change_info' => TRUE,
									'invite_users' => $command == 'mute' ? FALSE : TRUE,
									'pin_messages' => TRUE,
									'until_date' => $limit
								]
							]);
						}

						// Checking if the command is one of: /kick, /unban or /unmute
						if ($command == 'kick' || $command == 'unban' || $command == 'unmute') {
							yield $this -> channels -> editBanned([
								'channel' => $message['to_id']['chat_id'],
								'user_id' => $reply_message['from_id'],
								'banned_rights' => $chat['default_banned_rights']
							]);
						}

						if ($command == 'mute' && ($limit < 30 || $limit > 60 * 60 * 24 * 366)) {
							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $message['to_id']['chat_id'],
								'message' => 'You have muted <a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> forever.',
								'reply_to_msg_id' => $message['id'],
								'parse_mode' => 'HTML'
							]);
						}

						// Setting the verb of the report
						$verb = $command;

						// Checking if the command is one of: /ban or /unban
						if (preg_match('/^(un)?ban/miu', $command)) {
							$verb .= 'n';
						}

						// Checking if the command isn't one of: /mute or /unmute
						if (preg_match('/^(un)?mute$/miu', $command) != 1) {
							$verb .= 'e';
						}

						$verb .= 'd';

						// Sending the report to the channel
						$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $verb . ' <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a>' . $command == 'mute' && ($limit < 30 || $limit > 60 * 60 * 24 * 366) ? ' for ' . $args : '' . '.');
						break;
					case 'help':
						// Checking if the chat isn't a private chat
						if ($message['to_id']['_'] !== 'peerUser') {
							return;
						}

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $sender['id'],
							'message' => $this -> DB[$language]['help'],
							'parse_mode' => 'HTML',
							'reply_markup' => [
								'inline_keyboard' => $this -> get_keyboard('', $language)
							]
						]);
						break;
					case 'link':
						// Checking if the chat is a private chat
						if ($message['to_id']['_'] === 'peerUser') {
							return;
						}

						$chat = yield $this -> getPwrChat($message['to_id']['chat_id']);

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $message['to_id']['chat_id'],
							'message' => '<a href=\"' . $chat['invite'] . '\" >This</a> is the invite link to this chat.',
							'reply_to_msg_id' => $message['id'],
							'parse_mode' => 'HTML'
						]);
						break;
					case 'report':
						// Checking if the chat isn't a private chat
						if ($message['to_id']['_'] !== 'peerUser') {
							return;
						}

						/**
						* Checking if the user is an admin
						*
						* in_array() check if the array contains an item that match the element
						*/
						if (in_array($sender['id'], $this -> DB['admins']) == FALSE) {
							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $sender['id'],
								'message' => 'You can\'t use this command.',
								'parse_mode' => 'HTML'
							]);
							return;
						}

						/**
						* Retrieving the commands list and converting it into an array which element are a botCommand element
						*
						* array_map() converts the array by applying the closures to its elements
						*/
						$commands = array_map(function ($n) {
							return [
								'_' => 'botCommand',
								'command' => $n['name'],
								'description' => $n['description']
							];
						}, $this -> DB['commands']);

						yield $this -> bots -> setCommands([
							'commands' => $commands
						]);
						break;
					case 'start':
						// Checking if the chat isn't a private chat
						if ($message['to_id']['_'] !== 'peerUser') {
							return;
						}

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $sender['id'],
							'message' => str_replace('${sender_first_name}', $sender['first_name'], $this -> DB[$language]['welcome']),
							'parse_mode' => 'HTML',
							'reply_markup' => [
								'inline_keyboard' => $this -> get_keyboard('', $language)
							]
						]);
						break;
					default:
						break;
				}
			}
		}
	}

	$MadelineProto = new danog\MadelineProto\API('Bot.madeline', [
		'app_info' => [
			'lang_code' => 'en'
		],
		'logger' => [
			'logger' => danog\MadelineProto\Logger::FILE_LOGGER,
			'logger_level' => danog\MadelineProto\Logger::ULTRA_VERBOSE,
			'logger_param' => 'log/Bot.log'
		]
	]);

	// Starting the bot
	$MadelineProto -> startAndLoop(Bot::class);

	exit(0);
?>
