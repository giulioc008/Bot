<?php
	/**
	* Template for creating a Telegram bot in PHP.
	*
	* This template can be reused in accordance with the MIT license.
	*
	* @author		Giulio Coa
	* @copyright	2020- Giulio Coa <giuliocoa@gmail.com>
	* @license		https://www.linux.it/scegli-una-licenza/licenses/mit/
	*/

	// Installing the MadelineProto library
	/**
	* if (file_exists('madeline.php') == FALSE) {
	* 	copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
	* }
	* require_once 'madeline.php';
	*/
	require_once 'vendor/autoload.php';

	// Creating the bot
	class Bot extends danog\MadelineProto\EventHandler {
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
			$this -> DB = new mysqli('localhost', 'username', 'password', 'name', 3306);

			// Checking if there si some connection error
			if ($this -> DB -> connect_errno) {
				$this -> logger('Failed to make a MySQL connection, because ' . $this -> DB -> connect_error, \danog\MadelineProto\Logger::FATAL_ERROR);
				exit(1);
			}

			// Set autocommit to FALSE
			$this -> DB -> autocommit(FALSE);
		}

		/**
		* Handle updates from CallbackQuery
		*
		* @param array $update Update
		*
		* @return Generator
		*/
		public function onUpdateBotCallbackQuery(array $update) : Generator {
			$callback_data = trim(base64_decode($update['data']));

			// Retrieving the data of the user that pressed the button
			$sender = yield $this -> getInfo($update['user_id']);
			$sender = $sender['User'] ?? NULL;

			/**
			* Checking if the sender is a normal user
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
			if (empty($sender) || $sender['_'] !== 'user') {
				$this -> logger('The CallbackQuery ' . $update['query_id'] . ' wasn\'t managed because the sender isn\'t a normal user.');
				return;
			/**
			* Checking if the query is empty
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
			} else if (empty($callback_data)) {
				$this -> logger('The CallbackQuery ' . $update['query_id'] . ' wasn\'t managed because was empty.');
				return;
			}


			// Retrieving the language of the user
			$language = isset($sender['lang_code']) ? $sender['lang_code'] : 'en';

			// Checking if the language is supported
			$statement = $this -> DB -> prepare('SELECT NULL FROM `Languages` WHERE `lang_code`=?;');

			// Checking if the statement have errors
			if ($statement == FALSE) {
				$language = 'en';
			}

			// Completing the query
			$statement -> bind_param('s', $language);

			// Executing the query
			$result = $statement -> execute();

			// Checking if the query has product a result
			if ($result == FALSE) {
				$language = 'en';
			}

			// Closing the statement
			$statement -> close();

			// Setting the new keyboard
			$keyboard = [
				'_' => 'replyKeyboardMarkup',
				'resize' => FALSE,
				'single_use' => FALSE,
				'selective' => FALSE,
				'rows' => [
					[
						'_' => 'keyboardButtonRow',
						'buttons' => [
							[
								'_' => 'keyboardButton',
								'text' => ''
							]
						]
					],
					[
						'_' => 'keyboardButtonRow',
						'buttons' => [
							[
								'_' => 'keyboardButton',
								'text' => ''
							],
							[
								'_' => 'keyboardButton',
								'text' => ''
							]
						]
					]
				]
			];
			$keyboard = [
				'_' => 'replyInlineMarkup',
				'rows' => [
					[
						'_' => 'keyboardButtonRow',
						'buttons' => [
							[
								'_' => 'keyboardButtonUrl',
								'text' => '',
								'url' => ''
							]
						]
					],
					[
						'_' => 'keyboardButtonRow',
						'buttons' => [
							[
								'_' => 'keyboardButtonCallback',
								'text' => '',
								'data' => base64_encode('')
							],
							[
								'_' => 'keyboardButtonSwitchInline',
								'text' => '',
								'query' => '',
								'same_peer' => FALSE
							]
						]
					]
				]
			];

			yield $this -> messages -> editMessage([
				'no_webpage' => TRUE,
				'peer' => $update['peer'],
				'id' => $update['msg_id'],
				'message' => '',
				'reply_markup' => $keyboard,
				'parse_mode' => 'HTML'
			]);
		}

		/**
		* Handle updates from InlineQuery
		*
		* @param array $update Update
		*
		* @return Generator
		*/
		public function onUpdateBotInlineQuery(array $update) : Generator {
			$inline_query = trim($update['query']);
			$inline_query = htmlentities($inline_query, ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML5);
			$inline_query = mysqli_real_escape_string($this -> DB, $inline_query);

			// Retrieving the data of the user that sent the query
			$sender = yield $this -> getInfo($update['user_id']);
			$sender = $sender['User'] ?? NULL;

			/**
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
			if (empty($sender) || $sender['_'] !== 'user') {
				$this -> logger('The InlineQuery ' . $update['query_id'] . ' wasn\'t managed because the sender isn\'t a normal user.');
				return;
			/**
			* Checking if the query is empty
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
			} else if (empty($inline_query)) {
				$this -> logger('The InlineQuery ' . $update['query_id'] . ' wasn\'t managed because was empty.');
				return;
			// Checking if the query is long enough
			} else if (strlen($inline_query) < 3) {
				$this -> logger('The InlineQuery ' . $update['query_id'] . ' wasn\'t managed because was too short.');
				return;
			}


			// Retrieving the language of the user
			$language = isset($sender['lang_code']) ? $sender['lang_code'] : 'en';

			// Checking if the language is supported
			$statement = $this -> DB -> prepare('SELECT NULL FROM `Languages` WHERE `lang_code`=?;');

			// Checking if the statement have errors
			if ($statement == FALSE) {
				$language = 'en';
			}

			// Completing the query
			$statement -> bind_param('s', $language);

			// Executing the query
			$result = $statement -> execute();

			// Checking if the query has product a result
			if ($result == FALSE) {
				$language = 'en';
			}

			// Closing the statement
			$statement -> close();

			//Setting the answer
			$answer = [
				[
					'_' => 'inputBotInlineResult',
					'id' => uniqid(),
					'type' => 'article',
					'title' => '',
					'description' => '',
					'url' => '',
					'send_message' => [
						'_' => 'inputBotInlineMessageText',
						'no_webpage' => TRUE,
						'message' => ''
					]
				],
				[
					'_' => 'inputBotInlineResult',
					'id' => uniqid(),
					'type' => 'article',
					'title' => '',
					'description' => '',
					'url' => '',
					'send_message' => [
						'_' => 'inputBotInlineMessageText',
						'no_webpage' => TRUE,
						'message' => '',
						'reply_markup' => [
							'_' => 'replyInlineMarkup',
							'rows' => [
								[
									'_' => 'keyboardButtonRow',
									'buttons' => [
										[
											'_' => 'keyboardButtonUrl',
											'text' => '',
											'url' => ''
										]
									]
								],
								[
									'_' => 'keyboardButtonRow',
									'buttons' => [
										[
											'_' => 'keyboardButtonCallback',
											'text' => '',
											'data' => base64_encode('')
										],
										[
											'_' => 'keyboardButtonSwitchInline',
											'text' => '',
											'query' => '',
											'same_peer' => FALSE
										]
									]
								]
							]
						]
					]
				]
			];

			yield $this -> messages -> setInlineBotResults([
				'query_id' => mt_rand(),
				'results' => $answer,
				'cache_time' => 1,
				'switch_pm' => [
					'_' => 'inlineBotSwitchPM',
					'text' => '',
					'start_param' => ''
				]
			]);
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

			// Checking if the message is a normal message
			if ($message['_'] === 'messageEmpty') {
				$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was empty.');
				return;
			// Checking if the message is an incoming message
			} else if ($message['out'] ?? FALSE) {
				$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was an incoming message.');
				return;
			}

			// Retrieving the chat's data
			$chat = yield $this -> getPwrChat($message['to_id']['_'] === 'peerUser' ? $message['from_id'] : ($message['to_id']['_'] === 'peerChat' ? $message['to_id']['chat_id'] : $message['to_id']['channel_id']));

			// Checking if the chat is an allowed chat
			$statement = $this -> DB -> prepare('SELECT NULL FROM `Chats` WHERE `id`=?;');

			// Checking if the statement have errors
			if ($statement == FALSE) {
				$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
				return;
			}

			// Completing the query
			$statement -> bind_param('i', $chat['id']);

			// Executing the query
			$result = $statement -> execute();

			// Closing the statement
			$statement -> close();

			// Checking if the query have product a result
			if ($chat['type'] != 'user' && $chat['type'] != 'bot' && $result == FALSE) {
				$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from an unauthorized chat.');

				// Leaving the chat
				if ($chat['type'] == 'chat') {
					$bot = yield $this -> getSelf();

					$this -> messages -> deleteChatUser([
						'chat_id' => $chat['id'],
						'user_id' => $bot['id']
					]);
				} else {
					$this -> channels -> leaveChannel([
						'channel' => $chat['id']
					]);
				}

				$this -> logger('The bot have lefted the unauthorized chat.');
				return;
			}

			// Checking if the chat is a (super)group or a private chat
			if ($chat['type'] != 'user' && $chat['type'] != 'supergroup' && $chat['type'] != 'chat') {
				$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from a bot or a channel.');
				return;
			}

			// Checking if the message is a service message
			if ($message['_'] === 'messageService') {
				$answer = NULL;

				// Retrieving the welcome message
				$statement = $this -> DB -> prepare('SELECT `welcome` FROM `Chats` WHERE `id`=?;');

				// Checking if the statement haven't errors
				if ($statement) {
					// Completing the query
					$statement -> bind_param('i', $chat['id']);

					// Executing the query
					$statement -> execute();

					// Setting the output variables
					$statement -> bind_result($answer);

					// Retrieving the result
					$statement -> fetch();

					// Closing the statement
					$statement -> close();
				}

				// Checking if the service message is about new members
				if ($message['action']['_'] === 'messageActionChatAddUser') {
					$banned = [
						'multiple' = TRUE
					];

					/**
					* Checking if the welcome message is setted
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
					if (empty($answer) == FALSE) {
						$members = [];
					}

					// Cycle on the list of the new member
					foreach ($message['action']['users'] as $new_member) {
						// Downloading the user's informations from the Combot Anti-Spam API
						$result = execute_request('https://api.cas.chat/check?user_id=' . $new_member, TRUE);
						$result = json_decode($result, TRUE);

						// Retrieving the data of the new member
						$new_member = yield $this -> getInfo($new_member);
						$new_member = $new_member['User'] ?? NULL;

						/**
						* Checking if the user isn't a spammer, isn't a deleted account and is a normal user
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
						if ($result['ok'] == FALSE && empty($new_member) && $new_member['_'] === 'user' && $new_member['scam'] == FALSE && $new_member['deleted'] == FALSE) {
							/**
							* Checking if the welcome message is setted
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
							if (empty($answer) == FALSE) {
								$members []= $new_member;
							}
							continue;
						}

						$banned []= [
							'channel' => $update['chat_id'],
							'user_id' => $new_member['id'],
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

					/**
					* Checking if the welcome message is setted
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
					if (empty($answer) == FALSE) {
						$members = array_map(function ($n) {
							return '<a href=\"mention:' . $n['id'] . '\" >' . $n['first_name'] . '</a>';
						}, $members);
						$answer = str_replace('${mentions}', implode(', ', $members), $answer);

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $update['chat_id'],
							'message' => $answer,
							'parse_mode' => 'HTML'
						]);
					}
				} else if ($message['action']['_'] === 'messageActionChatJoinedByLink') {
					// Downloading the user's informations from the Combot Anti-Spam API
					$result = execute_request('https://api.cas.chat/check?user_id=' . $message['from_id'], TRUE);
					$result = json_decode($result, TRUE);

					// Retrieving the data of the new member
					$new_member = yield $this -> getInfo($message['from_id']);
					$new_member = $new_member['User'] ?? NULL;

					/**
					* Checking if the user is a spammer, is a deleted account or isn't a normal user
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
					if ($result['ok'] || empty($new_member) || $new_member['_'] !== 'user' || $new_member['scam'] || $new_member['deleted']) {
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

					/**
					* Checking if the welcome message is setted
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
					if (empty($answer) == FALSE) {
						$answer = str_replace('${mentions}', '<a href=\"mention:' . $new_member['id'] . '\" >' . $new_member['first_name'] . '</a>', $answer);

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $update['chat_id'],
							'message' => $answer,
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
				return;
			}

			$message['message'] = trim($message['message']);
			$message['message'] = htmlentities($message['message'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML5);
			$message['message'] = mysqli_real_escape_string($this -> DB, $message['message']);

			// Retrieving the data of the sender
			$sender = yield $this -> getInfo($message['from_id']);
			$sender = $sender['User'] ?? NULL;

			/**
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
			if (empty($sender) || $sender['_'] !== 'user') {
				$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because the sender isn\'t a normal user.');
				return;
			}

			// Checking if the user is in the bot's blacklist
			$statement = $this -> DB -> prepare('SELECT NULL FROM `Blacklist` WHERE `id`=?;');

			// Checking if the statement have errors
			if ($statement == FALSE) {
				$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
				return;
			}

			// Completing the query
			$statement -> bind_param('i', $sender['id']);

			// Executing the query
			$result = $statement -> execute();

			// Closing the statement
			$statement -> close();

			// Checking if the query has product a result
			if ($result) {
				$this -> logger('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> tried to use the bot.');
				return;
			}

			// Retrieving the language of the user
			$language = isset($sender['lang_code']) ? $sender['lang_code'] : 'en';

			// Checking if the language is supported
			$statement = $this -> DB -> prepare('SELECT NULL FROM `Languages` WHERE `lang_code`=?;');

			// Checking if the statement have errors
			if ($statement == FALSE) {
				$language = 'en';
			}

			// Completing the query
			$statement -> bind_param('s', $language);

			// Executing the query
			$result = $statement -> execute();

			// Checking if the query hasn't product a result
			if ($result == FALSE) {
				$language = 'en';
			}

			// Closing the statement
			$statement -> close();

			// Checking if is an @admin tag
			if (preg_match('/^\@admin([[:blank:]\n]{1}((\n|.)*))?$/miu', $message['message'], $matches)) {
				// Checking if the chat is a private chat
				if ($message['to_id']['_'] === 'peerUser') {
					$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from a private chat (@admin section).');
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
					return $n['user'];
				}, $admins);

				// Retrieving the admin message
				$statement = $this -> DB -> prepare('SELECT `admin_message` FROM `Languages` WHERE `lang_code`=?;');

				// Checking if the statement have errors
				if ($statement == FALSE) {
					$answer = '<a href=\"mention:${admin_id}\" >${admin_first_name}</a>,\n<a href=\"mention:${sender_id}\" >${sender_first_name}</a> needs your help${motive} into <a href=\"${chat_invite}\" >${chat_title}</a>.';
				}

				// Completing the query
				$statement -> bind_param('s', $language);

				// Executing the query
				$statement -> execute();

				// Setting the output variables
				$statement -> bind_result($answer);

				// Retrieving the result
				$statement -> fetch();

				// Closing the statement
				$statement -> close();

				/**
				* Checking if the admin message isn't setted
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
				if (empty($answer)) {
					$answer = '<a href=\"mention:${admin_id}\" >${admin_first_name}</a>,\n<a href=\"mention:${sender_id}\" >${sender_first_name}</a> needs your help${motive} into <a href=\"${chat_invite}\" >${chat_title}</a>.';
				}

				// Creating the message to send to the admins
				$answer = str_replace('${sender_id}', $sender['id'], $answer)
				$answer = str_replace('${sender_first_name}', $sender['first_name'], $answer)
				$answer = str_replace('${motive}', ($matches[2] ?? FALSE) ? ' for ' . $matches[2] : '', $answer)
				$answer = str_replace('${chat_invite}', $chat['invite'], $answer)
				$answer = str_replace('${chat_title}', $chat['title'], $answer)

				$message = [
					'multiple' => true
				];

				foreach ($admins as $user) {
					$message []= [
						'no_webpage' => TRUE,
						'peer' => $user['id'],
						'message' => str_replace('${admin_id}', $user['id'], str_replace('${admin_first_name}', $user['first_name'], $answer)),
						'parse_mode' => 'HTML'
					];
				}

				yield $this -> messages -> sendMessage($message);

				// Sending the report to the channel
				$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> has sent an @admin request into <a href=\"' . $chat['exported_invite'] . '\" >' . $chat['title'] . '</a>.');
			// Checking if is a bot command
			} else if (preg_match('/^\/([[:alnum:]\@]+)[[:blank:]]?([[:alnum:]]|[^\n]+)?$/miu', $message['message'], $matches)) {
				// Retrieving the command
				$command = explode('@', $matches[1])[0];
				$args = $matches[2] ?? NULL;

				switch ($command) {
					case 'add':
					case 'remove':
						// Checking if the chat is a private chat
						if ($message['to_id']['_'] === 'peerUser') {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from a private chat (/' . $command . ' section).');
							return;
						}

						// Checking if the sender is a bot's admin
						$statement = $this -> DB -> prepare('SELECT NULL FROM `Admins` WHERE `id`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Completing the query
						$statement -> bind_param('i', $sender['id']);

						// Executing the query
						$result = $statement -> execute();

						// Closing the statement
						$statement -> close();

						// Checking if the statement have errors
						if ($result == FALSE) {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from an unauthorized user (/' . $command . ' section).');
							return;
						}

						/**
						* Checking if the message isn't a message that replies to another message
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
						if (empty($message['reply_to_msg_id'] ?? NULL)) {
							// Retrieving the query
							$sql_query = $command == 'add' ? 'INSERT INTO `Chats` (`id`, `type`, `title`, `username`, `invite_link`, `welcome`, `staff_group`) VALUES (?, ?, ?, ?, ?, ?, ?);' : 'DELETE FROM `Chats` WHERE `id`=?;';
							$statement = $this -> DB -> prepare($sql_query);

							// Checking if the statement have errors
							if ($statement == FALSE) {
								$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
								return;
							}

							// Completing the query
							if ($command == 'add') {
								$statement -> bind_param('iss', $chat['id'], $chat['type'], $chat['title'], $chat['username'], $chat['invite'], NULL, NULL);
							} else {
								$statement -> bind_param('i', $chat['id']);
							}

							// Executing the query
							$statement -> execute()

							// Closing the statement
							$statement -> close();

							// Commit the change
							$this -> DB -> commit();

							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $chat['id'],
								'message' => 'Chat ' . $command . ($command == 'add' ? 'e' : '' . 'd.'),
								'reply_to_msg_id' => $message['id'],
								'parse_mode' => 'HTML'
							]);

							// Sending the report to the channel
							$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . ($command == 'add' ? 'e' : '') . 'd <a href=\"' . $chat['exported_invite'] . '\" >' . $chat['title'] . '</a> ' . ($command == 'add' ? 'into' : 'from') . ' the database.');
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
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because wasn\'t a message that replies to another message (/' . $command . ' section).');
							return;
						}

						$reply_message = $reply_message['messages'][0];

						// Retrieving the data of the user
						$user = yield $this -> getInfo($reply_message['from_id']);
						$user = $user['User'] ?? NULL;

						/**
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
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because the user of the reply_message isn\'t a normal user (/' . $command . ' section).');
							return;
						}

						// Retrieving the query
						$sql_query = $command == 'add' ? 'INSERT INTO `Admins` (`id`, `first_name`, `last_name`) VALUES (?, ?, ?);' : 'DELETE FROM `Admins` WHERE `id`=?;';
						$statement = $this -> DB -> prepare($sql_query);

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Completing the query
						if ($command == 'add') {
							$statement -> bind_param('iss', $user['id'], $user['first_name'], $user['last_name']);
						} else {
							$statement -> bind_param('i', $user['id']);
						}

						// Executing the query
						$statement -> execute()

						// Closing the statement
						$statement -> close();

						// Commit the change
						$this -> DB -> commit();

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $chat['id'],
							'message' => 'Admin ' . $command . ($command == 'add' ? 'e' : '' . 'd.'),
							'reply_to_msg_id' => $message['id'],
							'parse_mode' => 'HTML'
						]);

						// Sending the report to the channel
						$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command == 'add' ? 'assigned' : 'removed' . ' <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a> as bot\'s admin.');
						break;
					case 'announce':
						// Checking if the chat is a private chat
						if ($message['to_id']['_'] === 'peerUser') {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from a private chat (/' . $command . ' section).');
							return;
						}

						/**
						* Checking if the command have arguments
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
						if (empty($args) == FALSE) {
							$args = html_entity_decode($args, ENT_QUOTES | ENT_HTML5, 'UTF-8');

							// Checking if the sender is a bot's admin
							$statement = $this -> DB -> prepare('SELECT NULL FROM `Admins` WHERE `id`=?;');

							// Checking if the statement have errors
							if ($statement == FALSE) {
								$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
								return;
							}

							// Completing the query
							$statement -> bind_param('i', $sender['id']);

							// Executing the query
							$result = $statement -> execute();

							// Closing the statement
							$statement -> close();

							// Checking if is a serious use of the /announce command (command runned by a bot's admin)
							if ($result) {
								// Retrieving the staff group
								$statement = $this -> DB -> prepare('SELECT `id` FROM `Chats` WHERE `staff_group`=?;');

								// Checking if the statement have errors
								if ($statement == FALSE) {
									$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
									return;
								}

								// Completing the query
								$statement -> bind_param('i', $chat['id']);

								// Executing the query
								$result = $statement -> execute();

								// Checking if is a serious use of the /announce command (command runned in the staff group)
								if ($result) {
									// Setting the output variables
									$statement -> bind_result($result);

									// Retrieving the result
									$statement -> fetch();

									$messages = [
										'multiple' => true
									];

									// Cycle on the chats that have this staff group
									foreach ($result as $id) {
										$messages []= [
											'no_webpage' => TRUE,
											'peer' => $id,
											'message' => $args,
											'parse_mode' => 'HTML'
										];
									}

									yield $this -> messages -> sendMessage($messages);
								}

								// Closing the statement
								$statement -> close();
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
							if (in_array($sender['id'], $admins) == FALSE) {
								$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because the sender isn\'t an admin of the chat (/' . $command . ' section).');
								return;
							}

							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $chat['id'],
								'message' => $args,
								'parse_mode' => 'HTML'
							]);
						}
						break;
					case 'ban':
					case 'kick':
					case 'mute':
					case 'silence':
					case 'unban':
					case 'unmute':
					case 'unsilence':
						// Checking if the chat is a private chat
						if ($message['to_id']['_'] === 'peerUser') {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from a private chat (/' . $command . ' section).');
							return;
						}

						// Checking if the sender is a bot's admin
						$statement = $this -> DB -> prepare('SELECT NULL FROM `Admins` WHERE `id`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Completing the query
						$statement -> bind_param('i', $sender['id']);

						// Executing the query
						$result = $statement -> execute();

						// Closing the statement
						$statement -> close();

						// Checking if is a serious use of the /(un)ban command (command runned by a bot's admin)
						if (preg_match('/^(un)?ban/miu', $command) && $result) {
							// Retrieving the staff group
							$statement = $this -> DB -> prepare('SELECT `id` FROM `Chats` WHERE `staff_group`=?;');

							// Checking if the statement have errors
							if ($statement == FALSE) {
								$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
								return;
							}

							// Completing the query
							$statement -> bind_param('i', $chat['id']);

							// Executing the query
							$result = $statement -> execute();

							// Checking if is a serious use of the /(un)ban command (command runned in the staff group)
							if ($result) {
								// Setting the output variables
								$statement -> bind_result($result);

								// Retrieving the result
								$statement -> fetch();

								/**
								* Checking if the command haven't arguments
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
								if (empty($args) {
									yield $this -> messages -> sendMessage([
										'no_webpage' => TRUE,
										'peer' => $chat['id'],
										'message' => 'The syntax of the command is: <code>/' . $command . ' &lt;user_id|username&gt;</code>.',
										'reply_to_msg_id' => $message['id'],
										'parse_mode' => 'HTML'
									]);

									// Closing the statement
									$statement -> close();

									$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because the command have a wrong syntax (/' . $command . ' section).');
									return;
								}

								// Retrieving the data of the user
								$user = yield $this -> getInfo($args);
								$user = $user['User'] ?? NULL;

								/**
								* Checking if the user isn't a normal user
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
										'peer' => $chat['id'],
										'message' => 'The username/id is invalid.',
										'reply_to_msg_id' => $message['id'],
										'parse_mode' => 'HTML'
									]);

									$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because the command have a wrong syntax (/' . $command . ' section).');
									return;
								}

								// Cycle on the chats that have this staff group
								foreach ($result as $id) {
									// Retrieving the data of the chat
									$sub_chat = yield $this -> getInfo($id);
									$sub_chat = $sub_chat['Chat'] ?? NULL;

									/**
									* Checking if the chat isn't setted
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
									if (empty($sub_chat) || ($sub_chat['_'] != 'chat' && $sub_chat['_'] != 'channel') || ($sub_chat['_'] == 'channel' && $sub_chat['_']['broadcast'] == TRUE)) {
										continue;
									}

									yield $this -> channels -> editBanned([
										'channel' => $sub_chat['id'],
										'user_id' => $user['id'],
										'banned_rights' => $command == 'unban' ? $sub_chat['default_banned_rights'] : [
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
							}

							// Closing the statement
							$statement -> close();
							return;
						}

						// Setting limit to forever
						$limit = 0;

						/**
						* Checking if the command is /mute, if it has arguments and if the arguments are correct
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
						if ($command == 'mute' && empty($args) == FALSE && preg_match('/^([[:digit:]]+)[[:blank:]]?([[:alpha:]]+)$/miu', html_entity_decode($args, ENT_QUOTES | ENT_HTML5, 'UTF-8'), $matches)) {
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
									$limit *= 60 * 60 * 24 * 30;
								case 'a':
								case 'y':
								case 'anno':
								case 'year':
									// $limit *= 60 * 60 * 24 * 30 * 12;
									$limit *= 60 * 60 * 24 * 365;
									break;
								default:
									// Retrieving the mute message
									$statement = $this -> DB -> prepare('SELECT `mute_message` Languages `Chats` WHERE `lang_code`=?;');

									// Checking if the statement have errors
									if ($statement == FALSE) {
										$answer = 'The syntax of the command is: <code>/mute [time]</code>.\nThe <code>time</code> option must be more then 30 seconds and less of 366 days.';
									}

									// Completing the query
									$statement -> bind_param('s', $language);

									// Executing the query
									$statement -> execute();

									// Setting the output variables
									$statement -> bind_result($answer);

									// Retrieving the result
									$statement -> fetch();

									// Closing the statement
									$statement -> close();

									yield $this -> messages -> sendMessage([
										'no_webpage' => TRUE,
										'peer' => $chat['id'],
										'message' => $answer,
										'reply_to_msg_id' => $message['id'],
										'parse_mode' => 'HTML'
									]);
									break;
							}
						}

						/**
						* Checking if the message isn't a message that replies to another message
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
						if (empty($message['reply_to_msg_id'] ?? NULL)) {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because wasn\'t a message that replies to another message (/' . $command . ' section).');
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
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because wasn\'t a message that replies to another message (/' . $command . ' section).');
							return;
						}

						$reply_message = $reply_message['messages'][0];

						// Retrieving the data of the user
						$user = yield $this -> getInfo($reply_message['from_id']);
						$user = $user['User'] ?? NULL;

						/**
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
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because the user of the reply_message isn\'t a normal user (/' . $command . ' section).');
							return;
						}

						// Checking if the command is one of: /ban, /kick or /mute
						if ($command == 'ban' || $command == 'kick' || $command == 'mute') {
							yield $this -> channels -> editBanned([
								'channel' => $chat['id'],
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
								'channel' => $chat['id'],
								'user_id' => $reply_message['from_id'],
								'banned_rights' => $chat['default_banned_rights']
							]);
						}

						// Checking if is a /(un)silence command
						if (preg_match('/^(un)?silence/miu', $command)) {
							yield $this -> messages -> editChatDefaultBannedRights([
								'peer' => $chat['id'],
								'banned_rights' => [
									'_' => 'chatBannedRights',
									'view_messages' => FALSE,
									'send_messages' => $command == 'unsilence' ? FALSE : TRUE,
									'send_media' => $command == 'unsilence' ? FALSE : TRUE,
									'send_stickers' => $command == 'unsilence' ? FALSE : TRUE,
									'send_gifs' => $command == 'unsilence' ? FALSE : TRUE,
									'send_games' => $command == 'unsilence' ? FALSE : TRUE,
									'send_inline' => $command == 'unsilence' ? FALSE : TRUE,
									'embed_links' => $command == 'unsilence' ? FALSE : TRUE,
									'send_polls' => $command == 'unsilence' ? FALSE : TRUE,
									'change_info' => TRUE,
									'invite_users' => FALSE,
									'pin_messages' => TRUE,
									'until_date' => 0
								]
							]);
						}

						// Checking if is a permanent /mute command
						if ($command == 'mute' && ($limit < 30 || $limit > 60 * 60 * 24 * 366)) {
							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $chat['id'],
								'message' => 'You have muted <a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> forever.',
								'reply_to_msg_id' => $message['id'],
								'parse_mode' => 'HTML'
							]);
						}

						// Setting the verb of the report
						$verb = $command;

						// Checking if the command is one of: /ban or /unban
						if (preg_match('/^(un)?ban/miu', $command)) {
							$verb .= 'ne';
						} else if ($command == 'kick') {
							$verb .= 'e';
						}

						$verb .= 'd';

						// Sending the report to the channel
						$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $verb . ' <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a>' . ($command == 'mute' && $limit > 30 && $limit < 60 * 60 * 24 * 366 ? ' for ' . $args : '') . '.');
						break;
					case 'blacklist':
					case 'unblacklist':
						// Checking if the chat is a private chat
						if ($message['to_id']['_'] === 'peerUser') {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from a private chat (/' . $command . ' section).');
							return;
						}

						// Checking if the sender is an admin
						$statement = $this -> DB -> prepare('SELECT NULL FROM `Admins` WHERE `id`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Completing the query
						$statement -> bind_param('i', $sender['id']);

						// Executing the query
						$result = $statement -> execute();

						// Closing the statement
						$statement -> close();

						if ($result == FALSE) {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from an unauthorized user (/' . $command . ' section).');
							return;
						}

						/**
						* Checking if the message isn't a message that replies to another message
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
						if (empty($message['reply_to_msg_id'] ?? NULL)) {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because wasn\'t a message that replies to another message (/' . $command . ' section).');
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
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because wasn\'t a message that replies to another message (/' . $command . ' section).');
							return;
						}

						$reply_message = $reply_message['messages'][0];

						// Retrieving the data of the user
						$user = yield $this -> getInfo($reply_message['from_id']);
						$user = $user['User'] ?? NULL;

						/**
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
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because the user of the reply_message isn\'t a normal user (/' . $command . ' section).');
							return;
						}

						// Insert the user into the blacklist
						$statement = $this -> DB -> prepare('INSERT INTO `Blacklist` (`id`) VALUES (?);');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Completing the query
						$statement -> bind_param('i', $user['id']);

						// Executing the query
						$statement -> execute();

						// Closing the statement
						$statement -> close();

						// Commit the change
						$this -> DB -> commit();

						// Sending the report to the channel
						$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . 'ed <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a>.');
						break;
					case 'help':
						// Checking if the chat isn't a private chat
						if ($message['to_id']['_'] !== 'peerUser') {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because wasn\'t a message from a private chat (/' . $command . ' section).');
							return;
						}

						// Retrieving the help message
						$statement = $this -> DB -> prepare('SELECT `help_message` FROM `Languages` WHERE `lang_code`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$answer = '<b>FREQUENTLY ASKED QUESTION<\b>\n(FAQ list)\n\n<a href=\"(link to the manual, without brackets)\" >TELEGRAM GUIDE</a>\n\n<b>INLINE COMMANDS<\b>\n(Inline mode description)';
						}

						// Completing the query
						$statement -> bind_param('s', $language);

						// Executing the query
						$statement -> execute();

						// Setting the output variables
						$statement -> bind_result($answer);

						// Retrieving the result
						$statement -> fetch();

						// Closing the statement
						$statement -> close();

						/**
						* Checking if the help message is setted
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
						if (empty($answer)) {
							$answer = '<b>FREQUENTLY ASKED QUESTION<\b>\n(FAQ list)\n\n<a href=\"(link to the manual, without brackets)\" >TELEGRAM GUIDE</a>\n\n<b>INLINE COMMANDS<\b>\n(Inline mode description)';
						}

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $sender['id'],
							'message' => $answer,
							'parse_mode' => 'HTML'
						]);
						break;
					case 'link':
						// Checking if the chat is a private chat
						if ($message['to_id']['_'] === 'peerUser') {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from a private chat (/' . $command . ' section).');
							return;
						}

						// Retrieving the help message
						$statement = $this -> DB -> prepare('SELECT `link_message` FROM `Languages` WHERE `lang_code`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$answer = '<a href=\"${invite_link}\" >This</a> is the invite link to this chat.';
						}

						// Completing the query
						$statement -> bind_param('s', $language);

						// Executing the query
						$statement -> execute();

						// Setting the output variables
						$statement -> bind_result($answer);

						// Retrieving the result
						$statement -> fetch();

						// Closing the statement
						$statement -> close();

						/**
						* Checking if the help message isn't setted
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
						if (empty($answer)) {
							$answer = '<a href=\"${invite_link}\" >This</a> is the invite link to this chat.';
						}

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $chat['id'],
							'message' => str_replace('${invite_link}', $chat['invite'], $answer),
							'reply_to_msg_id' => $message['id'],
							'parse_mode' => 'HTML'
						]);
						break;
					case 'report':
						// Checking if the chat isn't a private chat
						if ($message['to_id']['_'] !== 'peerUser') {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because wasn\'t a message from a private chat (/' . $command . ' section).');
							return;
						}

						// Checking if the sender is an admin
						$statement = $this -> DB -> prepare('SELECT NULL FROM `Admins` WHERE `id`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Completing the query
						$statement -> bind_param('i', $sender['id']);

						// Executing the query
						$result = $statement -> execute();

						// Closing the statement
						$statement -> close();

						if ($result == FALSE) {
							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $sender['id'],
								'message' => 'You can\'t use this command.',
								'parse_mode' => 'HTML'
							]);

							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because was a message from an unauthorized user (/' . $command . ' section).');
							return;
						}

						yield $this -> bots -> setCommands([
							'commands' => [
								[
									'_' => 'botCommand',
									'command' => 'add',
									'description' => 'Assign a user as bot\'s admin'
								],
								[
									'_' => 'botCommand',
									'command' => 'announce',
									'description' => 'If it\'s used into the staff group, send an announce in all the groups, otherwise only in the group where it\'s used'
								],
								[
									'_' => 'botCommand',
									'command' => 'ban',
									'description' => 'If it\'s used into the staff group, ban a user from all the groups, otherwise only from the group where it\'s used'
								],
								[
									'_' => 'botCommand',
									'command' => 'blacklist',
									'description' => 'Insert a user in the bot\'s blacklist'
								],
								[
									'_' => 'botCommand',
									'command' => 'check',
									'description' => 'Print the database for checking it'
								],
								[
									'_' => 'botCommand',
									'command' => 'help',
									'description' => 'Send the help of the bot'
								],
								[
									'_' => 'botCommand',
									'command' => 'kick',
									'description' => 'Kick a user from a group'
								],
								[
									'_' => 'botCommand',
									'command' => 'link',
									'description' => 'Send the invite link of the (super)group'
								],
								[
									'_' => 'botCommand',
									'command' => 'mute',
									'description' => 'Mute a user in a group'
								],
								[
									'_' => 'botCommand',
									'command' => 'remove',
									'description' => 'Remove a user as bot\'s admin'
								],
								[
									'_' => 'botCommand',
									'command' => 'report',
									'description' => 'Set the bot commands'
								],
								[
									'_' => 'botCommand',
									'command' => 'silence',
									'description' => 'Mute a group, except the admins'
								],
								[
									'_' => 'botCommand',
									'command' => 'start',
									'description' => 'Starts the bot'
								],
								[
									'_' => 'botCommand',
									'command' => 'unban',
									'description' => 'If it\'s used into the staff group, unban a user from all the groups, otherwise only from the group where it\'s used'
								],
								[
									'_' => 'botCommand',
									'command' => 'unblacklist',
									'description' => 'Remove a user from the bot\'s blacklist'
								],
								[
									'_' => 'botCommand',
									'command' => 'unmute',
									'description' => 'Unmute a user from a group'
								],
								[
									'_' => 'botCommand',
									'command' => 'unsilence',
									'description' => 'Unmute a group'
								],
								[
									'_' => 'botCommand',
									'command' => 'update',
									'description' => 'Update the database'
								]
							]
						]);
						break;
					case 'start':
						// Checking if the chat isn't a private chat
						if ($message['to_id']['_'] !== 'peerUser') {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because wasn\'t a message from a private chat (/' . $command . ' section).');
							return;
						}

						// Retrieving the start message
						$statement = $this -> DB -> prepare('SELECT `start_message` FROM `Languages` WHERE `lang_code`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$answer = 'Hello <a href=\"mention:${sender_id}\" >${sender_first_name}</a>, welcome !\n\n(Rest of the message to be sent upon receipt of the start command)';
						}

						// Completing the query
						$statement -> bind_param('s', $language);

						// Executing the query
						$statement -> execute();

						// Setting the output variables
						$statement -> bind_result($answer);

						// Retrieving the result
						$statement -> fetch();

						// Closing the statement
						$statement -> close();

						/**
						* Checking if the start message isn't setted
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
						if (empty($answer)) {
							$answer = 'Hello <a href=\"mention:${sender_id}\" >${sender_first_name}</a>, welcome !\n\n(Rest of the message to be sent upon receipt of the start command)';
						}

						$answer = str_replace('${sender_id}', $sender['id'], $answer);
						$answer = str_replace('${sender_first_name}', $sender['first_name'], $answer);

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $sender['id'],
							'message' => $answer,
							'parse_mode' => 'HTML'
						]);
						break;
					case 'update':
						// Checking if the chat isn't a private chat
						if ($message['to_id']['_'] !== 'peerUser') {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because wasn\'t a message from a private chat (/' . $command . ' section).');
							return;
						}

						$banned = [
							'multiple' = TRUE
						];

						// Retrieving the chats' list
						$result = $this -> DB -> query('SELECT `id`, `type` FROM `Chats`;');

						// Checking if the query is failed
						if ($result == FALSE) {
							$this -> logger('Failed to make the query, because ' . $this -> DB -> error, \danog\MadelineProto\Logger::ERROR);
							return;
						}

						$chats = $result -> fetch_all(MYSQLI_ASSOC);

						$result -> free();

						$statement = $this -> DB -> prepare('UPDATE `Chats` SET `type`=?, `title`=?, `username`=?, `invite_link`=? WHERE `id`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Cycle on the list of the chats
						foreach ($chats as $DB_chat) {
							// Retrieving the data of the chat
							$DB_chat = yield $this -> getInfo($DB_chat['id']);
							$DB_chat = $DB_chat['Chat'] ?? NULL;

							/**
							* Checking if the chat is a group that is migrated to a supergroup
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
							if (empty($DB_chat) == FALSE && $DB_chat['_'] === 'chat' && $DB_chat['migrated_to']['_'] !== 'inputChannelEmpty') {
								// Closing the statement
								$statement -> close();

								$old_id = $DB_chat['id'];
								$DB_chat = yield $this -> getPwrChat($DB_chat['migrated_to']['channel_id']);

								$statement = $this -> DB -> prepare('UPDATE `Chats` SET `id`=?, `type`=?, `title`=?, `username`=?, `invite_link`=? WHERE `id`=?;');

								// Checking if the statement have errors
								if ($statement == FALSE) {
									$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);

									$statement = $this -> DB -> prepare('UPDATE `Chats` SET `type`=?, `title`=?, `username`=?, `invite_link`=? WHERE `id`=?;');

									// Checking if the statement have errors
									if ($statement == FALSE) {
										$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
										return;
									}
									continue;
								}

								$statement -> bind_param('issssi', $DB_chat['id'], $DB_chat['type'], $DB_chat['title'], $DB_chat['username'], $DB_chat['invite'], $old_id);

								// Executing the query
								$statement -> execute()

								// Closing the statement
								$statement -> close();

								// Commit the change
								$this -> DB -> commit();

								$statement = $this -> DB -> prepare('UPDATE `Chats` SET `type`=?, `title`=?, `username`=?, `invite_link`=? WHERE `id`=?;');

								// Checking if the statement have errors
								if ($statement == FALSE) {
									$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
									return;
								}
								continue;
							}

							$statement -> bind_param('ssssi', $DB_chat['type'], $DB_chat['title'], $DB_chat['username'], $DB_chat['invite'], $DB_chat['id']);

							// Executing the query
							$result = $statement -> execute()
						}

						// Closing the statement
						$statement -> close();

						// Commit the change
						$this -> DB -> commit();

						/**
						* Retrieving the (super)groups list
						*
						* array_filter() filters the array by the type of each chat
						* array_map() convert each chat to its id
						*/
						$chats = array_filter($chats, function ($n) {
							return $n['type'] == 'supergroup' || $n['type'] == 'chat';
						});
						$chats = array_map(function ($n) {
							return $n['id'];
						}, $chats);

						// Cycle on the list of the (super)groups
						foreach ($chats as $DB_chat) {
							// Retrieving the data of the chat
							$DB_chat = yield $this -> getPwrChat($DB_chat);

							/**
							* Retrieving the members' list of the chat
							*
							* array_filter() filters the array by the type of each member
							* array_map() convert each member to its id
							*/
							$members = array_filter($DB_chat['participants'], function ($n) {
								return $n['role'] == 'user';
							});
							$members = array_map(function ($n) {
								return $n['user']['id'];
							}, $members);

							// Cycle on the list of the members
							foreach ($members as $member) {
								// Downloading the user's informations from the Combot Anti-Spam API
								$result = execute_request('https://api.cas.chat/check?user_id=' . $member, TRUE);
								$result = json_decode($result, TRUE);

								// Retrieving the data of the new member
								$member = yield $this -> getInfo($member);
								$member = $member['User'] ?? NULL;

								/**
								* Checking if the user isn't a spammer, isn't a deleted account and is a normal user
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
								if ($result['ok'] == FALSE && empty($member) && $member['_'] === 'user' && $member['scam'] == FALSE && $member['deleted'] == FALSE) {
									continue;
								}

								$banned []= [
									'channel' => $update['chat_id'],
									'user_id' => $member['id'],
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
						}

						yield $this -> channels -> editBanned($banned);

						/**
						* Retrieving the admins' list
						*
						* array_map() convert admin to its id
						*/
						$result = $this -> DB -> query('SELECT `id` FROM `Admins`;');

						// Checking if the query is failed
						if ($result == FALSE) {
							$this -> logger('Failed to make the query, because ' . $this -> DB -> error, \danog\MadelineProto\Logger::ERROR);
							return;
						}

						$admins = $result -> fetch_all(MYSQLI_ASSOC);
						$result -> free();

						$admins = array_map(function ($n) {
							return $n['id'];
						}, $admins);

						$statement = $this -> DB -> prepare('UPDATE `Admins` SET `first_name`=?, `last_name`=? WHERE `id`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Cycle on the list of the admins
						foreach ($admins as $id) {
							$admin = yield $this -> getInfo($id);
							$admin = $admin['User'] ?? NULL;

							/**
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
							if (empty($admin) || $admin['_'] !== 'user') {
								// Closing the statement
								$statement -> close();

								$statement = $this -> DB -> prepare('DELETE FROM `Admins` WHERE `id`=?;');

								// Checking if the statement have errors
								if ($statement == FALSE) {
									$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);

									$statement = $this -> DB -> prepare('UPDATE `Admins` SET `first_name`=?, `last_name`=? WHERE `id`=?;');

									// Checking if the statement have errors
									if ($statement == FALSE) {
										$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
										return;
									}
									continue;
								}

								$statement -> bind_param('i', $id);

								// Executing the query
								$statement -> execute()

								// Closing the statement
								$statement -> close();

								// Commit the change
								$this -> DB -> commit();

								$statement = $this -> DB -> prepare('UPDATE `Admins` SET `first_name`=?, `last_name`=? WHERE `id`=?;');

								// Checking if the statement have errors
								if ($statement == FALSE) {
									$this -> logger('Failed to make the query, because ' . $statement -> error, \danog\MadelineProto\Logger::ERROR);
									return;
								}
								continue;
							}

							$statement -> bind_param('ssi', $admin['first_name'], $admin['last_name'], $admin['id']);

							// Executing the query
							$result = $statement -> execute()
						}

						// Closing the statement
						$statement -> close();

						// Commit the change
						$this -> DB -> commit();

						// Retrieving the update message
						$statement = $this -> DB -> prepare('SELECT `update_message` FROM `Languages` WHERE `lang_code`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$answer = 'Database updated.';
						}

						// Completing the query
						$statement -> bind_param('s', $language);

						// Executing the query
						$statement -> execute();

						// Setting the output variables
						$statement -> bind_result($answer);

						// Retrieving the result
						$statement -> fetch();

						// Closing the statement
						$statement -> close();

						/**
						* Checking if the update message isn't setted
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
						if (empty($answer)) {
							$answer = 'Database updated.';
						}

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $sender['id'],
							'message' => $answer,
							'parse_mode' => 'HTML'
						]);
						break;
					default:
						// Checking if the chat isn't a private chat
						if ($message['to_id']['_'] !== 'peerUser') {
							$this -> logger('The Message ' . $update['id'] . ' wasn\'t managed because wasn\'t a message from a private chat (/' . $command . ' section).');
							return;
						}

						// Retrieving the unknown message
						$statement = $this -> DB -> prepare('SELECT `unknown_message` FROM `Languages` WHERE `lang_code`=?;');

						// Checking if the statement have errors
						if ($statement == FALSE) {
							$answer = 'This command isn\'t supported.';
						}

						// Completing the query
						$statement -> bind_param('s', $language);

						// Executing the query
						$statement -> execute();

						// Setting the output variables
						$statement -> bind_result($answer);

						// Retrieving the result
						$statement -> fetch();

						// Closing the statement
						$statement -> close();

						/**
						* Checking if the unknown message isn't setted
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
						if (empty($answer)) {
							$answer = 'This command isn\'t supported.';
						}

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $sender['id'],
							'message' => $answer,
							'parse_mode' => 'HTML'
						]);
						break;
				}

				// Checking if the chat isn't a private chat
				if ($message['to_id']['_'] !== 'peerUser') {
					yield $this -> channels -> deleteMessages([
						'revoke' => TRUE,
						'id' => [
							$message['id']
						]
					]);
				}
			}
		}
	}

	$MadelineProto = new danog\MadelineProto\API('Bot.madeline', [
		'app_info' => [
			'lang_code' => 'en'
		],
		'db' => [
			'type' => 'mysql',
			'dbType' => [
				'host' => 'localhost',
				'port' => 3306,
				'user' => 'username',
				'password' => 'password',
				'database' => 'database_name',
				'idle_timeout' => 60 * 60 * 24 * 30 * 12
			]
		]
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
