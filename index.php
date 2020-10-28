<?php
/**
 * Template for creating a Telegram bot in PHP.
 *
 * This file contains the core of the bot.
 *
 * @author		Giulio Coa

 * @copyright	2020- Giulio Coa

 * @license		https://choosealicense.com/licenses/lgpl-3.0/ LGPL version 3
 */

/**
 * Adding the libraries
 *
 * file_exists() checks if a file, or directory, exists
 * copy() copies a file
 */
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	require_once('vendor/autoload.php');
} else {
	copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
	require_once('madeline.php');
}

/**
 * The bot class.
 */
class Bot extends danog\MadelineProto\EventHandler {
	/**
	 * @var $DB The database.
	 */
	private $DB;
	/**
	 * @var array $tmp A support variable.
	 * @internal
	 */
	private array $tmp;
	/**
	 * @var int $button_InlineKeyboard Determine how many buttons an InlineKeyboard must contains.
	 */
	private int $button_InlineKeyboard;

	/**
	* @internal Create the bitmask for the chat permissions.
	*
	* @param array $permissions The chat permissions.
	*
	* @return int
	*/
	private function bitmask(array $permissions) : int {
		$bitmask = 0;

		// Checking if is a correct use of the function
		if ($permissions['_'] === 'chatBannedRights') {
			/**
			* Creating the bitmask
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
			$bitmask |= empty($permissions['send_messages']) === FALSE && $permissions['send_messages'] ? 0: 1 << 10;
			$bitmask |= empty($permissions['send_media']) === FALSE && $permissions['send_media'] ? 0: 1 << 9;
			$bitmask |= empty($permissions['send_stickers']) === FALSE && $permissions['send_stickers'] ? 0: 1 << 8;
			$bitmask |= empty($permissions['send_gifs']) === FALSE && $permissions['send_gifs'] ? 0: 1 << 7;
			$bitmask |= empty($permissions['send_games']) === FALSE && $permissions['send_games'] ? 0: 1 << 6;
			$bitmask |= empty($permissions['send_inline']) === FALSE && $permissions['send_inline'] ? 0: 1 << 5;
			$bitmask |= empty($permissions['embed_links']) === FALSE && $permissions['embed_links'] ? 0: 1 << 4;
			$bitmask |= empty($permissions['send_polls']) === FALSE && $permissions['send_polls'] ? 0: 1 << 3;
			$bitmask |= empty($permissions['change_info']) === FALSE && $permissions['change_info'] ? 0: 1 << 2;
			$bitmask |= empty($permissions['invite_users']) === FALSE && $permissions['invite_users'] ? 0: 1 << 1;
			$bitmask |= empty($permissions['pin_messages']) === FALSE && $permissions['pin_messages'] ? 0: 1 << 0;
		}

		return $bitmask;
	}

	/**
	* @internal Retrieve the info about a chat/channel/user.
	*
	* @param int $id The id of the chat/channel/user that we want retrieve the info.
	*
	* @return mixed
	*/
	private function getInfos(int $id) {
		// Retrieving the data of the user
		$user = yield $this -> getInfo($id);

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
		if (empty($user['User'] ?? NULL) || $user['User']['_'] !== 'user') {
			$user = $user['Chat'] ?? NULL;

			/**
			* Checking if the chat is a normal chat
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
			if (empty($user) || ($user['_'] !== 'chat' && $user['_'] !== 'channel') || ($user['_'] === 'channel' && empty($user['megagroup']))) {
				$this -> logger('The retrieval was unsuccessful (' . $id . ').');
				return NULL;
			}
		} else {
			$user = $user['User'];
		}

		return $user;
	}

	/**
	* @internal Retrieve the a message.
	*
	* @param array $input_messages An array composed by the id of the messages that we want retrieve.
	*
	* @return array
	*/
	private function getMessages(array $input_messages) : ?array {
		$messages = yield $this -> messages -> getMessages([
			'id' => $input_messages
		]);

		// Checking if the result is valid
		if ($messages['_'] === 'messages.messagesNotModified') {
			/**
			* Encode the messages
			*
			* json_encode() Convert the PHP object to a JSON string
			*/
			$messages = json_encode($input_messages);

			$this -> logger('Message retrieval was unsuccessful (' . $messages . ').');
			return NULL;
		}

		/**
		* Retrieving the messages
		*
		* array_filter() filters the array by the type of each message
		*/
		$admins = array_filter($message['messages'], function ($n) {
			return $n['_'] === 'message';
		});

		return $message['messages'];
	}

	/**
	* @internal Retrieve the language of the user and check if the bot supports it.
	*
	* @param string $language The language of the user.
	*
	* @return string
	*/
	private function getLanguage(string $language) : string {
		/**
		* Retrieving the language of the user
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
		$language = empty($language) === FALSE ? $language : 'en';

		// Checking if the language is supported
		try {
			yield $this -> DB -> execute('SELECT NULL FROM `Languages` WHERE `lang_code`=?;', [
				$language
			]);
		} catch (Amp\Sql\QueryError $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);
			$language = 'en';
		} catch (Amp\Sql\FailureException $e) {
			$language = 'en';
		}

		return $language;
	}

	/**
	* @internal Retrieve an output message from the database.
	*
	* @param string $language The language of the user.
	* @param string $message_name The name of the message.
	* @param string $default_message The default message.
	*
	* @return string
	*/
	private function getOutputMessage(string $language, string $message_name, string $default_message) : string {
		// Retrieving the message
		try {
			$answer = yield $this -> DB -> execute('SELECT ? FROM `Languages` WHERE `lang_code`=?;', [
				$message_name,
				$language
			]);
		} catch (Amp\Sql\QueryError $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);
			$answer = $default_message;
		} catch (Amp\Sql\FailureException $e) {
			$answer = $default_message;
		}

		// Checking if the query has product a result
		if ($answer instanceof Amp\Mysql\ResultSet) {
			yield $answer -> advance();
			$answer = $answer -> getCurrent();
			$answer = $answer[$message_name];
		}

		/**
		* Checking if the message isn't setted
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
			$answer = $default_message;
		}

		return $answer;
	}

	/**
	* @internal Check if a user is already into the database (Chats_data section) and modify its data.
	* In case the user isn't into the database, adds it.
	*
	* @param int $chat_id The id of the chat/channel that the user joined.
	* @param int $user_id The id of the user.
	*
	* @return void
	*/
	private function insertChatsData(int $chat_id, int $user_id) {
		// Checking if the user was be a member
		try {
			$result = yield $this -> DB -> execute('SELECT `entrances` FROM `Chats_data` WHERE `user_id`=? AND `chat_id`=?;', [
				$user_id,
				$chat_id
			]);
		} catch (Amp\Sql\QueryError $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			return;
		} catch (Amp\Sql\FailureException $e) {
			// Opening a transaction
			$transaction = yield $this -> DB -> beginTransaction();

			try {
				yield $transaction -> execute('INSERT INTO `Chats_data` (`chat_id`, `user_id`) VALUES (?, ?);', [
					$chat_id,
					$user_id
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				return;
			}

			// Commit the change
			yield $transaction -> commit();

			// Closing the transaction
			yield $transaction -> close();
		}

		// Checking if the query hasn't product a result
		if ($result instanceof Amp\Mysql\ResultSet === FALSE) {
			return;
		}

		yield $result -> advance();
		$result = $result -> getCurrent();
		$result = $result['entrances'] + 1;

		// Opening a transaction
		$transaction = yield $this -> DB -> beginTransaction();

		// Insert the data of the user
		try {
			yield $transaction -> execute('UPDATE `Chats_data` SET `ttl`=\"NULL\",  `entrances`=?, `last_join`=? WHERE `user_id`=? AND `chat_id`=?;', [
				$result,
				/**
				* Retrieving the actual time
				*
				* date() return the actual datetime with the given format
				*/
				date('Y-m-d H:i:s'),
				$user_id,
				$chat_id
			]);
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			return;
		}

		// Commit the change
		yield $transaction -> commit();

		// Closing the transaction
		yield $transaction -> close();
	}

	/**
	* @internal Updates the database.
	*
	* @return void
	*/
	private function update() {
		$banned = [
			'multiple' => TRUE
		];

		// Retrieving the users' list
		try {
			$result = yield $this -> DB -> query('SELECT `id` FROM `Users`;');
		} catch (Amp\Sql\FailureException $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			return;
		}

		$users = [];

		// Cycle on the result
		while (yield $result -> advance()) {
			$sub_user = $result -> getCurrent();

			// Retrieving the data of the user
			$sub_user = $this -> getInfos($sub_user['id']);

			/**
			* Checking if the user is empty
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
			if (empty($sub_user)) {
				continue;
			}

			$users []= $sub_user;
		}

		// Opening a transaction
		$transaction = yield $this -> DB -> beginTransaction();

		// Updating the chats' data
		try {
			$statement = yield $transaction -> prepare('UPDATE `Users` SET `first_name`=?, `last_name`=?, `lang_code`=? WHERE `id`=?;');
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

			// Closing the transaction
			yield $transaction -> close();
			return;
		}

		// Cycle on the list of the chats
		foreach ($users as $sub_user) {
			try {
				yield $statement -> execute([
					$sub_user['first_name'],
					$sub_user['first_name'],
					$this -> getLanguage($sub_user['lang_code']),
					$sub_user['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
		}

		// Closing the statement
		$statement -> close();

		// Commit the change
		yield $transaction -> commit();

		// Closing the transaction
		yield $transaction -> close();

		// Retrieving the chats' list
		try {
			$result = yield $this -> DB -> query('SELECT `id` FROM `Chats`;');
		} catch (Amp\Sql\FailureException $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			return;
		}

		$chats = [];

		// Cycle on the result
		while (yield $result -> advance()) {
			$sub_chat = $result -> getCurrent();

			// Retrieving the data of the chat
			$sub_chat = $this -> getInfos($sub_chat['id']);

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
			if (empty($sub_chat)) {
				continue;
			}

			$chats []= $sub_chat;
		}

		// Opening a transaction
		$transaction = yield $this -> DB -> beginTransaction();

		// Updating the chats' data
		try {
			$statement = yield $transaction -> prepare('UPDATE `Chats` SET `title`=?, `username`=?, `invite_link`=?, `permissions`=? WHERE `id`=?;');
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

			// Closing the transaction
			yield $transaction -> close();
			return;
		}

		// Cycle on the list of the chats
		foreach ($chats as $sub_chat) {
			if ($sub_chat['_'] === 'chat' && $sub_chat['migrated_to']['_'] !== 'inputChannelEmpty') {
				$old_id = $sub_chat['id'];

				$sub_chat = yield $this -> getPwrChat($sub_chat['migrated_to']['channel_id']);

				$permissions = $this -> getInfos($sub_chat['id']);

				/**
				* Checking if the chat is a normal chat
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
				if (empty($permissions)) {
					$this -> logger('The update ' . $sub_chat['id'] . ' of wasn&apos;t complete because the retrieve process of the default permissions of the chat failed.');
				}

				$bitmask = bitmask($permissions['default_banned_rights']);

				// Closing the statement
				$statement -> close();

				try {
					yield $transaction -> execute('UPDATE `Chats` SET `id`=?, `type`=?, `title`=?, `username`=?, `invite_link`=?, `permissions`=? WHERE `id`=?;', [
						$sub_chat['id'],
						$sub_chat['type'],
						$sub_chat['title'],
						$sub_chat['username'],
						$sub_chat['invite'],
						$bitmask,
						$old_id
					]);
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

					try {
						$statement = yield $transaction -> prepare('UPDATE `Chats` SET `title`=?, `username`=?, `invite_link`=?, `permissions`=? WHERE `id`=?;');
					} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

						// Closing the transaction
						yield $transaction -> close();
						return;
					}
					continue;
				}

				// Commit the change
				$transaction -> commit();

				try {
					$statement = yield $transaction -> prepare('UPDATE `Chats` SET `title`=?, `username`=?, `invite_link`=?, `permissions`=? WHERE `id`=?;');
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

					// Closing the transaction
					yield $transaction -> close();
					return;
				}
				continue;
			}

			$bitmask = bitmask($sub_chat['default_banned_rights']);

			$sub_chat = yield $this -> getPwrChat($sub_chat['id']);

			try {
				yield $statement -> execute([
					$sub_chat['title'],
					$sub_chat['username'],
					$sub_chat['invite'],
					$bitmask,
					$sub_chat['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
		}

		// Closing the statement
		$statement -> close();

		// Commit the change
		yield $transaction -> commit();

		// Closing the transaction
		yield $transaction -> close();

		/**
		* Retrieving the (super)groups/channels list
		*
		* array_filter() filters the array by the type of each chat
		* array_map() convert each chat to its id
		*/
		$chats = array_filter($chats, function ($n) {
			return $n['type'] !== 'bot' && $n['type'] !== 'user';
		});
		$chats = array_map(function ($n) {
			return $n['id'];
		}, $chats);

		// Cycle on the list of the (super)groups/channels
		foreach ($chats as $sub_chat) {
			/**
			* Retrieving the members' list of the chat
			*
			* array_filter() filters the array by the type of each member
			* array_map() convert each member to its id
			*/
			$members = array_filter($sub_chat['participants'], function ($n) {
				return $n['role'] === 'user';
			});
			$members = array_map(function ($n) {
				return $n['user']['id'];
			}, $members);

			// Cycle on the list of the members
			foreach ($members as $member) {
				/**
				* Downloading the user's informations from the Combot Anti-Spam API
				*
				* json_decode() convert a JSON string into a PHP variables
				*/
				$result = yield $this -> getHttpClient() -> request(new Amp\Http\Client\Request('https://api.cas.chat/check?user_id=' . $member));

				// Retrieving the result
				$result = yield $result -> getBody() -> buffer();

				$result = json_decode($result, TRUE);

				// Retrieving the data of the new member
				$member = $this -> getInfos($member);

				/**
				* Checking if the user isn't a spammer and isn't a deleted account
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
				if ($result['ok'] === FALSE && empty($member) && $member['scam'] === FALSE && $member['deleted'] === FALSE) {
					continue;
				}

				$banned []= [
					'channel' => $sub_chat['id'],
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

				// Opening a transaction
				$transaction = yield $this -> DB -> beginTransaction();

				// Removing the data of the user
				try {
					yield $transaction -> execute('DELETE FROM `Chats_data` WHERE `user_id`=?;', [
						$member['id']
					]);
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				}
				try {
					yield $transaction -> execute('DELETE FROM `Penalty` WHERE `user_id`=?;', [
						$member['id']
					]);
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				}

				// Commit the change
				yield $transaction -> commit();

				// Closing the transaction
				yield $transaction -> close();
			}
		}

		yield $this -> channels -> editBanned($banned);

		/**
		* Retrieving the admins' list
		*
		* array_map() convert admin to its id
		*/
		try {
			$result = yield $this -> DB -> query('SELECT `id` FROM `Admins`;');
		} catch (Amp\Sql\FailureException $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			return;
		}

		$admins = [];

		// Cycle on the result
		while (yield $result -> advance()) {
			$admins []= $result -> getCurrent();
		}

		$admins = array_map(function ($n) {
			return $n['id'];
		}, $admins);

		// Opening a transaction
		$transaction = yield $this -> DB -> beginTransaction();

		// Updating the admins' data
		try {
			$statement = yield $transaction -> prepare('DELETE FROM `Admins` WHERE `id`=?;');
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

			// Closing the transaction
			yield $transaction -> close();
			return;
		}

		// Cycle on the list of the admins
		foreach ($admins as $id) {
			$admin = $this -> getInfos($id);

			/**
			* Checking if the admin isn't a deleted account
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
			if (empty($admin) === FALSE && $admin['deleted'] === FALSE) {
				continue;
			}

			try {
				yield $statement -> execute([
					$admin['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				continue;
			}

			// Removing the data of the user
			try {
				yield $transaction -> execute('DELETE FROM `Chats_data` WHERE `user_id`=?;', [
					$admin['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
			try {
				yield $transaction -> execute('DELETE FROM `Penalty` WHERE `user_id`=?;', [
					$admin['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
			try {
				yield $transaction -> execute('DELETE FROM `Penalty` WHERE `execute_by`=?;', [
					$admin['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
		}

		// Closing the statement
		$statement -> close();

		// Commit the change
		yield $transaction -> commit();

		// Closing the transaction
		yield $transaction -> close();
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
	public function onStart() {
		// Setting the database
		try {
			$this -> DB = yield Amp\Mysql\connect(Amp\Mysql\ConnectionConfig::fromString('host=' . 'localhost' . ';user=' . 'username' . ';pass=' . 'password' . ';db=' . 'database_name'));
		} catch (Amp\Sql\ConnectionException $e) {
			$this -> logger('The connection with the MySQL database is failed for ' . $e -> getMessage() . '.', danog\MadelineProto\Logger::FATAL_ERROR);
			exit(1);
		}

		// Set the character set
		$this -> DB -> setCharset('utf8mb4_bin', 'utf8mb4_unicode_ci');

		$this -> tmp = [];

		// Setting how many buttons an InlineKeyboard must contains (button_InlineKeyboard = #row  * 2)
		$this -> button_InlineKeyboard = 2  * 4;

		// Executing, every minute, the check of the TTL of Messages_to_delete
		Amp\Loop::repeat(1000  * 60, function () use ($this) {
			try {
				$result = yield $this -> DB -> execute('SELECT `id` FROM `Messages_to_delete` WHERE `ttl`=?;', [
					/**
					 * Retrieving the actual datetime
					 *
					 * date() return the actual datetime with the given format
					 */
					date('Y-m-d H:i:s')
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				return;
			}

			// Checking if the query has product a result
			if ($result instanceof Amp\Mysql\ResultSet) {
				$messages = [];

				// Cycle on the result
				while (yield $result -> advance()) {
					$messages []= $result -> getCurrent();
				}

				/**
				 * Converting the messages to its id
				 *
				 * array_map() convert each message to a its id
				 */
				$messages = array_map(function ($n) {
					return $n['id'];
				}, $messages);

				// Opening a transaction
				$transaction = yield $this -> DB -> beginTransaction();

				try {
					$statement = yield $transaction -> prepare('DELETE FROM `Messages_to_delete` WHERE `id`=?;');
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					// Closing the transaction
					yield $transaction -> close();
					return;
				}

				yield $this -> channels -> deleteMessages([
					'revoke' => TRUE,
					'id' => $messages
				]);

				// Cycle on the list of the messages
				foreach ($messages as $message) {
					try {
						yield $statement -> execute([
							$message
						]);
					} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					}
				}

				// Closing the statement
				$statement -> close();

				// Commit the change
				yield $transaction -> commit();

				// Closing the transaction
				yield $transaction -> close();
			}
		});

		// Executing, every day, the check of the TTL of Chats_data
		Amp\Loop::repeat(1000  * 60  * 60  * 24, function ($database) {
			try {
				$result = yield $database -> execute('SELECT `chat_id`, `user_id` FROM `Chats_data` WHERE `ttl`=?;', [
					/**
					 * Retrieving the actual datetime
					 *
					 * date() return the actual datetime with the given format
					 */
					date('Y-m-d H:i:s')
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				return;
			}

			// Checking if the query has product a result
			if ($result instanceof Amp\Mysql\ResultSet) {
				$data = [];

				// Cycle on the result
				while (yield $result -> advance()) {
					$data []= $result -> getCurrent();
				}

				// Opening a transaction
				$transaction = yield $database -> beginTransaction();

				try {
					$statement = yield $transaction -> prepare('DELETE FROM `Chats_data` WHERE `chat_id`=? AND `user_id`=?;');
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					// Closing the transaction
					yield $transaction -> close();
					return;
				}

				// Cycle on the list of the data
				foreach ($data as $single_data) {
					try {
						yield $statement -> execute([
							$single_data['chat_id'],
							$single_data['user_id']
						]);
					} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					}
				}

				// Closing the statement
				$statement -> close();

				// Commit the change
				yield $transaction -> commit();

				// Closing the transaction
				yield $transaction -> close();
			}
		}, $this -> DB);

		// Executing, every two days, the update the database
		Amp\Loop::repeat(1000  * 60  * 60  * 24  * 2, $this -> update());

		$experience_loop = new danog\Loop\Generic\GenericLoop(function () {
			/**
			 * Setting the experience bonus/malus
			 *
			 * random_int() generate a random integer number
			 */
			$experience = random_int(-50, 100);

			// Checking if is an empty turn
			if ($experience === 0) {
				/**
				 * Generating the delay for the next execution
				 *
				 * random_int() generate a random integer number
				 */
				return random_int(1000,  PHP_INT_MAX);
			}

			$positive_text = [
				''
			];
			$negative_text = [
				''
			];

			// Retrieving the list of chat members
			try {
				$result = yield $this -> DB -> query('SELECT `id` FROM `Chats`;');
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);

				/**
				 * Generating the delay for the next execution
				 *
				 * random_int() generate a random integer number
				 */
				return random_int(1000,  PHP_INT_MAX);
			}

			$chats = [];

			// Cycle on the result
			while (yield $result -> advance()) {
				$sub_chat = $result -> getCurrent();

				// Retrieving the data of the chat
				$sub_chat = $this -> getInfos($sub_chat['id']);

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
				if (empty($sub_chat)) {
					continue;
				}

				$chats []= yield $this -> getPwrChat($sub_chat['id']);
			}

			/**
			 * Extract the chat
			 *
			 * random_int() generate a random integer number
			 * count() retrieve the length of the array
			 */
			$chat = $chats[random_int(0, count($chats) - 1)];

			/**
			 * Retrieving the members' list of the chat
			 *
			 * array_filter() filters the array by the type of each member
			 * array_map() convert each member to its id
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
			$members = array_filter($chat['participants'], function ($n) {
				return $n['role'] === 'user';
			});
			$members = array_map(function ($n) {
				return $this -> getInfos($n['user']['id']);
			}, $members);
			$members = array_filter($members, function ($n) {
				return empty($n) === FALSE && $n['deleted'] === FALSE && $n['scam'] === FALSE;
			});

			/**
			 * Extract the fortunate user
			 *
			 * random_int() generate a random integer number
			 * count() retrieve the length of the array
			 */
			$user = $members[random_int(0, count($members) - 1)];

			// Retrieving the old experience
			try {
				$result = yield $this -> DB -> execute('SELECT `experience` FROM `Chats_data` WHERE `chat_id`=? AND `user_id`=?;', [
					$chat['id'],
					$user['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);

				// Opening a transaction
				$transaction = yield $this -> DB -> beginTransaction();

				// Improving the experience
				try {
					$result = yield $transaction -> execute('INSERT INTO `Chats_data` (`chat_id`, `user_id`, `experience`) VALUES (?, ?, ?);', [
						$chat['id'],
						$user['id'],
						$new_experience > 0 ? $new_experience : 0
					]);
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);

					// Closing the transaction
					yield $transaction -> close();

					/**
					 * Generating the delay for the next execution
					 *
					 * random_int() generate a random integer number
					 */
					return random_int(1000,  PHP_INT_MAX);
				}

				// Commit the change
				yield $transaction -> commit();

				// Closing the transaction
				yield $transaction -> close();

				/**
				 * Generating the delay for the next execution
				 *
				 * random_int() generate a random integer number
				 */
				return random_int(1000,  PHP_INT_MAX);
			}

			yield $result -> advance();
			$new_experience = $result -> getCurrent();
			$new_experience = $new_experience['experience'] + $experience;

			// Checking if the experience must be resetted
			if ($new_experience < 0) {
				$new_experience = 0;
			}

			// Opening a transaction
			$transaction = yield $this -> DB -> beginTransaction();

			// Improving the experience
			try {
				$result = yield $transaction -> execute('UPDATE `Chats_data` SET `experience`=? WHERE `chat_id`=? AND `user_id`=?;', [
					$new_experience,
					$chat['id'],
					$user['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);

				// Closing the transaction
				yield $transaction -> close();

				/**
				 * Generating the delay for the next execution
				 *
				 * random_int() generate a random integer number
				 */
				return random_int(1000,  PHP_INT_MAX);
			}

			// Commit the change
			yield $transaction -> commit();

			// Closing the transaction
			yield $transaction -> close();

			// Retrieve the history of the chat
			/**
			 * @todo Complete the function with the code retrieves the entire history of a chat/channel.
			 */
			$messages = [];

			$text_list = $positive_text;
			if ($experience < 0){
				$text_list = $negative_text;
			}

			/**
			 * Extract the text
			 *
			 * random_int() generate a random integer number
			 * count() retrieve the length of the array
			 */
			$text = $text_list[random_int(0, count($text_list) - 1)];

			/**
			 * Personalizing the message
			 *
			 * str_replace() replace the tags with their value
			 * abs() convert the argument to its absolute value
			 */
			$text = str_replace('${name}', '<a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a>', $text);
			$text = str_replace('${experience}', abs($experience), $text);

			// Searching the last message sent by the user
			/**
			 * @todo Complete the function with the code retrieves the entire history of a chat/channel.
			 */
			foreach ($messages as $message) {
				if ($message['from_id'] == $user['id']) {
					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $chat['id'],
						'message' => $answer,
						'reply_to_msg_id' => $message['id'],
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);
					break;
				}
			}

			/**
			 * Generating the delay for the next execution
			 *
			 * random_int() generate a random integer number
			 */
			return random_int(1000,  PHP_INT_MAX);
		}, 'experience_loop');

		$experience_loop -> start();

		/**
		 * Generating the delay for the next execution
		 *
		 * random_int() generate a random integer number
		 */
		yield delay(random_int(1000,  PHP_INT_MAX));
	}

	/**
	 * Handle updates from CallbackQuery
	 *
	 * @param array $update Update
	 *
	 * @return Generator
	 */
	public function onUpdateBotCallbackQuery(array $update) : Generator {
		// Retrieving the data of the user that pressed the button
		$sender = $this -> getInfos($update['user_id'])

		/**
		 * Checking if the user is empty
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
		if (empty($sender) === FALSE) {
			$this -> logger('The CallbackQuery ' . $update['query_id'] . ' wasn&apos;t generated by a normal user.');
			return;
		/**
		 * Checking if the query is empty
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
		} else if (empty($callback_data)) {
			$this -> logger('The CallbackQuery ' . $update['query_id'] . ' wasn&apos;t managed because was empty.');
			return;
		}

		/**
		 * Retrieving the callback data
		 *
		 * trim() strip whitespaces from the begin and the end of the string
		 * base64_decode() decode the string
		 */
		$callback_data = trim(base64_decode($update['data']));

		/**
		 * Retrieving the command that have generated the CallbackQuery
		 *
		 * explode() convert a string into an array
		 */
		$command = explode('/', $callback_data)[0];

		// Retrieving the message associated to the CallbackQuery
		$message = $this -> getMessages([
			$update['msg_id']
		]);

		/**
		 * Checking if the message is empty
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
		if (empty($message)) {
			$this -> logger('The CallbackQuery ' . $update['query_id'] . ' wasn&apos;t associated to a message.');
			return;
		}

		$message = $message[0];

		// Retrieving the language of the user
		$language = $this -> getLanguage($sender['lang_code']);

		// Setting the new keyboard
		switch ($command) {
			case 'staff_group':
				// Checking if the sender is a bot's admin
				try {
					yield $this -> DB -> execute('SELECT NULL FROM `Admins` WHERE `id`=?;', [
						$sender['id']
					]);
				} catch (Amp\Sql\QueryError $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);
					return;
				} catch (Amp\Sql\FailureException $e) {
					$this -> logger('The CallbackQuery ' . $update['query_id'] . ' wasn&apos;t managed because was a message from an unauthorized user (/' . $command . ' section).');
					return;
				}

				// Retrieving the chats' list
				try {
					$result = yield $this -> DB -> query('SELECT `id`, `title` FROM `Chats`;');
				} catch (Amp\Sql\FailureException $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);
					return;
				}

				$chats = [];

				// Cycle on the result
				while (yield $result -> advance()) {
					$chats []= $result -> getCurrent();
				}

				/**
				 * Retrieving the query
				 *
				 * explode() convert a string into an array
				 */
				$query = explode('/', $callback_data)[1];

				switch ($query) {
					case 'page':
						/**
						 * Retrieving the page
						 *
						 * explode() convert a string into an array
						 */
						$actual_page = (int) explode('/', $callback_data)[2];

						/**
						 * Retrieving the button in the page
						 *
						 * array_splice() extract the sub-array from the main array
						 */
						$chats = array_slice($chats, $actual_page  * $this -> button_InlineKeyboard,  $this -> button_InlineKeyboard);

						/**
						 * Setting the InlineKeyboard
						 *
						 * array_map() convert each chat to a keyboardButtonCallback
						 */
						$chats = array_map(function ($n) {
							return [
								'_' => 'keyboardButtonCallback',
								'text' => $n['title'],
								/**
								 * Generating the keyboardButtonCallback data
								 *
								 * base64_encode() encode the string
								 */
								'data' => base64_encode($command . '/' . $n['id'] . '/no')
							];
						}, $chats);

						$row = [
							'_' => 'keyboardButtonRow',
							'buttons' => []
						];
						$keyboard = [
							'_' => 'replyInlineMarkup',
							'rows' => []
						];

						// Cycle on the buttons' list
						foreach ($chats as $button) {
							/**
							 * Retrieving the length of the row
							 *
							 * count() retrieve the length of the array
							 */
							if (count($row['buttons']) === 2) {
								// Saving the row
								$keyboard['rows'] []= $row;

								// Creating a new row
								$row['buttons'] = [];
							}
							// Adding a button to the row
							$row['buttons'] []= $button;
						}

						// Setting the page
						$keyboard['rows'] []= [
							'_' => 'keyboardButtonRow',
							'buttons' => [
								[
									'_' => 'keyboardButtonCallback',
									'text' => $actual_page != 0 ? 'Previous page' : '',
									/**
									 * Generating the keyboardButtonCallback data
									 *
									 * base64_encode() encode the string
									 */
									'data' => base64_encode($actual_page !== 0 ? $command . '/page/' . $actual_page - 1 : '')
								],
								[
									'_' => 'keyboardButtonCallback',
									'text' => ($actual_page + 1)  * $this -> button_InlineKeyboard > $total ? 'Next page' : '',
									/**
									 * Generating the keyboardButtonCallback data
									 *
									 * base64_encode() encode the string
									 */
									'data' => base64_encode(($actual_page + 1)  * $this -> button_InlineKeyboard > $total ? $command . '/page/' . $actual_page + 1 : '')
								]
							]
						];

						// Setting the confirm buttons
						$keyboard['rows'] []= [
							'_' => 'keyboardButtonRow',
							'buttons' => [
								[
									'_' => 'keyboardButtonCallback',
									'text' => 'Reject',
									/**
									 * Generating the keyboardButtonCallback data
									 *
									 * base64_encode() encode the string
									 */
									'data' => base64_encode($command . '/reject')
								],
								[
									'_' => 'keyboardButtonCallback',
									'text' => 'Confirm',
									/**
									 * Generating the keyboardButtonCallback data
									 *
									 * base64_encode() encode the string
									 */
									'data' => base64_encode($command . '/confirm')
								]
							]
						];
						break;
					case 'reject':
						// Retrieving the reject message
						$answer = $this -> getOutputMessage($language, 'reject_message', 'Operation deleted.');

						/**
						 * Checking if is an abort
						 *
						 * array_key_exists() check if the key exists
						 */
						if (array_key_exists('staff_group', $this -> tmp) && array_key_exists($update['peer'], $this -> tmp['staff_group'])) {
							/**
							 * Removing the id from the array
							 *
							 * array_search() search the id into the array
							 * array_splice() extract the sub-array from the main array
							 */
							array_splice($this -> tmp, array_search($update['peer'], $this -> tmp['staff_group']), 1);

							/**
							 * Checking if there isn't other request for the /staff_group command
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
							if (empty($this -> tmp['staff_group'])) {
								/**
								 * Removing the 'staff_group' key from the array
								 *
								 * array_search() search the 'staff_group' key into the array
								 * array_splice() extract the sub-array from the main array
								 */
								array_splice($this -> tmp, array_search('staff_group', $this -> tmp), 1);
							}
						}

						yield $this -> messages -> editMessage([
							'no_webpage' => TRUE,
							'peer' => $update['peer'],
							'id' => $message['id'],
							'message' => $answer,
							'reply_markup' => [],
							'parse_mode' => 'HTML'
						]);
						return;
					case 'confirm':
						/**
						 * Checking if the confirm is pressed for error
						 *
						 * array_key_exists() check if the key exists
						 */
						if (array_key_exists('staff_group', $this -> tmp) === FALSE || array_key_exists($update['peer'], $this -> tmp['staff_group']) === FALSE) {
							$this -> logger('The CallbackQuery ' . $update['query_id'] . ' wasn&apos;t managed because the sender have pressed the wrong button (' . $command . ' section).');
							return;
						}

						// Opening a transaction
						$transaction = yield $this -> DB -> beginTransaction();

						// Updating the staff_group for the selected chats
						try {
							$statement = yield $transaction -> prepare('UPDATE `Chats` SET `staff_group`=? WHERE `id`=?;');
						} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
							$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Cycle on the selected chats
						foreach ($this -> tmp['staff_group'][$update['peer']] as $id) {
							try {
								yield $statement -> execute([
									$update['peer'],
									$id
								]);
							} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
								$this -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);
							}
						}

						// Closing the statement
						$statement -> close();

						// Commit the change
						yield $transaction -> commit();

						// Closing the transaction
						yield $transaction -> close();

						/**
						 * Removing the id from the array
						 *
						 * array_search() search the id into the array
						 * array_splice() extract the sub-array from the main array
						 */
						array_splice($this -> tmp, array_search($update['peer'], $this -> tmp['staff_group']), 1);

						/**
						 * Checking if there isn't other request for the /staff_group command
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
						if (empty($this -> tmp['staff_group'])) {
							/**
							 * Removing the 'staff_group' key from the array
							 *
							 * array_search() search the 'staff_group' key into the array
							 * array_splice() extract the sub-array from the main array
							 */
							array_splice($this -> tmp, array_search('staff_group', $this -> tmp), 1);
						}

						// Retrieving the confirm message
						$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

						yield $this -> messages -> editMessage([
							'no_webpage' => TRUE,
							'peer' => $update['peer'],
							'id' => $message['id'],
							'message' => $answer,
							'reply_markup' => [],
							'parse_mode' => 'HTML'
						]);
						break;
					default:
						// Retrieving the InlineKeyboard
						$keyboard = $message['reply_markup'];

						/**
						 * Retrieving the type of the request
						 *
						 * explode() convert a string into an array
						 */
						$type = explode('/', $callback_data)[2];

						// Cycle on the rows
						foreach ($keyboard['rows'] as $row) {
							// Cycle on the buttons
							foreach ($row['buttons'] as $button) {
								// Checking if the button is what is pressed
								if ($button['data'] == $update['data']) {
									/**
									 * Commuting the button
									 *
									 * base64_encode() encode the string
									 * str_replace() replace the special characters with their code
									 */
									$button['data'] = base64_encode(str_replace($type, $type === 'yes' ? 'no' : 'yes', $callback_data));
									$button['text'] = $type === 'yes' ? str_replace(' ✅', '', $button['text']) : $button['text'] . ' ✅';

									// Checking if is a select request
									if ($type === 'yes') {
										/**
										 * Checking if the first /staff_group request
										 *
										 * array_key_exists() check if the 'staff_group' key exists
										 */
										if (array_key_exists('staff_group', $this -> tmp)) {
											/**
											 * Checking if the first /staff_group request for this staff group
											 *
											 * array_key_exists() check if the id exists
											 */
											if (array_key_exists($update['peer'], $this -> tmp['staff_group'])) {
												$this -> tmp['staff_group'][$update['peer']] []= $query;
											} else {
												$this -> tmp['staff_group'] []= [
													$update['peer'] => [
														$query
													];
												];
											}
										} else {
											$this -> tmp []= [
												'staff_group' => [
													$update['peer'] => [
														$query
													]
												]
											];
										}
									} else {
										/**
										 * Removing the query from the array
										 *
										 * array_search() search the query into the array
										 * array_splice() extract the sub-array from the main array
										 */
										array_splice($this -> tmp['staff_group'][$update['peer']], array_search($query, $this -> tmp['staff_group'][$update['peer']]), 1);
									}
									break;
								}
							}

							if ($button['data'] == $update['data']) {
								break;
							}
						}
						break;
				}

				yield $this -> messages -> editMessage([
					'peer' => $update['peer'],
					'id' => $message['id'],
					'reply_markup' => $keyboard
				]);
				break;
			default:
				break;
		}
	}

	/**
	 * Handle updates from InlineQuery
	 *
	 * @param array $update Update
	 *
	 * @return Generator
	 */
	public function onUpdateBotInlineQuery(array $update) : Generator {
		/**
		 * Encode the text
		 *
		 * trim() strip whitespaces from the begin and the end of the string
		 * htmlentities() convert all HTML character to its safe value
		 */
		$inline_query = trim($update['query']);
		$inline_query = htmlentities($inline_query, ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML5, 'UTF-8');

		// Retrieving the data of the user that sent the query
		$sender = ($this, $update['user_id']);

		/**
		 * Checking if the user is empty
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
		if (empty($sender)) {
			$this -> logger('The InlineQuery ' . $update['query_id'] . ' wasn&apos;t managed because the sender isn&apos;t a normal user.');
			return;
		/**
		 * Checking if the query is empty
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
		} else if (empty($inline_query)) {
			$this -> logger('The InlineQuery ' . $update['query_id'] . ' wasn&apos;t managed because was empty.');
			return;
		/**
		 * Checking if the query is long enough
		 *
		 * strlen() return the length of the string
		 */
		} else if (strlen($inline_query) < 3) {
			$this -> logger('The InlineQuery ' . $update['query_id'] . ' wasn&apos;t managed because was too short.');
			return;
		}


		// Retrieving the language of the user
		$language = $this -> getLanguage($sender['lang_code']);

		//Setting the answer
		$answer = [
			[
				'_' => 'inputBotInlineResult',
				/**
				 * Generating the inputBotInlineResult id
				 *
				 * uniqid() generate a random string
				 */
				'id' => uniqid(),
				'type' => 'article',
				'title' => '',
				'description' => '',
				'url' => '',
				'send_message' => [
					'_' => 'inputBotInlineMessageText',
					'no_webpage' => TRUE,
					'message' => '',
					'reply_markup' => []
				]
			]
		];

		yield $this -> messages -> setInlineBotResults([
			/**
			 * Generating the query id
			 *
			 * random_int() generate a random integer number
			 */
			'query_id' => random_int(),
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
			$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was empty.');
			return;
		// Checking if the message is an incoming message
		} else if ($message['out'] ?? FALSE) {
			$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was an incoming message.');
			return;
		}

		// Retrieving the chat's data
		$chat = yield $this -> getPwrChat($message['to_id']['_'] === 'peerUser' ? $message['from_id'] : ($message['to_id']['_'] === 'peerChat' ? $message['to_id']['chat_id'] : $message['to_id']['channel_id']));

		// Retrieving the data of the sender
		$sender = $this -> getInfos($message['from_id']);

		/**
		 * Checking if the user is empty
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
		if (empty($sender)) {
			$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the sender isn&apos;t a normal user.');
			return;
		}

		// Checking if the message is a service message
		if ($message['_'] === 'messageService') {
			$answer = NULL;

			// Retrieving the welcome message
			try {
				$answer = yield $this -> DB -> execute('SELECT `welcome` FROM `Chats` WHERE `id`=?;', [
					$chat['id']
				]);
			} catch (Amp\Sql\QueryError $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			} catch (Amp\Sql\FailureException $e) {
			}

			// Checking if the query has product a result
			if ($answer instanceof Amp\Mysql\ResultSet) {
				yield $answer -> advance();
				$answer = $answer -> getCurrent();
				$answer = $answer['welcome'];
			}

			// Checking if the service message is about new members added by a user
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
				if (empty($answer) === FALSE) {
					$members = [];
				}

				// Retrieving the bot's data
				$this = yield $this -> getSelf();

				/**
				 * Checking if the bot have joined the (super)group
				 *
				 * in_array() check if the bot id is into the array
				 */
				if (in_array($this['id'], $message['action']['users'])) {
					/**
					 * Removing the bot id from the new members list
					 *
					 * array_search() search the bot id into the array
					 * array_splice() extract the sub-array from the main array
					 */
					array_splice($message['action']['users'], array_search($this['id'], $message['action']['users']), 1);

					// Checking if who added the bot is a bot's admin
					try {
						yield $this -> DB -> execute('SELECT NULL FROM `Admins` WHERE `id`=?;', [
							$sender['id']
						]);
					} catch (Amp\Sql\QueryError $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					} catch (Amp\Sql\FailureException $e) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from an unauthorized chat.');

						// Leaving the chat
						if ($chat['type'] == 'chat') {
							$this -> messages -> deleteChatUser([
								'chat_id' => $chat['id'],
								'user_id' => $this['id']
							]);
						} else if ($chat['type'] == 'channel' || $chat['type'] == 'supergroup') {
							$this -> channels -> leaveChannel([
								'channel' => $chat['id']
							]);
						}

						$this -> logger('The bot have lefted the unauthorized chat.');
						return;
					}

					// Retrieving the welcome message
					$result = $this -> getOutputMessage($language, 'welcome_message', 'Thank you for adding me to the group.' . "\n" . 'If you want to set up a welcome message, use the /welcome command' . "\n\n" . '<b>N.B.</b>: If you want to insert a newline in messages, you must encode it as <code>\n</code>.');

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $chat['id'],
						'message' => $result,
						'reply_to_msg_id' => $message['id'],
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);
				}

				// Cycle on the list of the new member
				foreach ($message['action']['users'] as $new_member) {
					/**
					 * Downloading the user's informations from the Combot Anti-Spam API
					 *
					 * json_decode() convert a JSON string into a PHP variables
					 */
					$result = yield $this -> getHttpClient() -> request(new Amp\Http\Client\Request('https://api.cas.chat/check?user_id=' . $new_member));

					// Retrieving the result
					$result = yield $result -> getBody() -> buffer();

					$result = json_decode($result, TRUE);

					// Retrieving the data of the new member
					$user = $this -> getInfos($new_member);

					/**
					 * Checking if the new member is empty
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
					if (empty($user)) {
						$banned []= [
							'channel' => $update['chat_id'],
							'user_id' => $new_member,
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
						continue;
					// Checking if the user isn't a spammer and isn't a deleted account
					} else if ($result['ok'] === FALSE && $user['scam'] === FALSE && $user['deleted'] === FALSE) {
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
						if (empty($answer) === FALSE) {
							$members []= $user;
						}

						// Insert the data of the user
						$this -> insertChatsData($update['chat_id'], $user['id']);

						continue;
					}

					$banned []= [
						'channel' => $update['chat_id'],
						'user_id' => $user['id'],
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
				if (empty($answer) === FALSE) {
					/**
					 * Personalizing the message
					 *
					 * array_map() convert each new member into it's tag
					 * implode() convert the array into a string
					 * str_replace() replace the 'mentions' tag with the string
					 */
					$members = array_map(function ($n) {
						return '<a href=\"mention:' . $n['id'] . '\" >' . $n['first_name'] . '</a>';
					}, $members);
					$answer = str_replace('${mentions}', implode(', ', $members), $answer);

					$welcome = yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $update['chat_id'],
						'message' => $answer,
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);

					$welcome = $welcome['updates'][0]['message'];

					// Opening a transaction
					$transaction = yield $this -> DB -> beginTransaction();

					// Insert the message
					try {
						yield $transaction -> execute('INSERT INTO `Messages_to_delete` (`id`, `ttl`) VALUES (?, ?);', [
							$welcome['id'],
							/**
							 * Retrieving the TTL (5 minutes in the future)
							 *
							 * date() return the actual datetime with the given format
							 * mktime() create the datetime
							 */
							date('Y-m-d H:i:s', mktime(date('G'), date('i') + 5))
						]);
					} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					}

					// Commit the change
					yield $transaction -> commit();

					// Closing the transaction
					yield $transaction -> close();
				}
			// Checking if the service message is about new members that have joined the chat throught its invite link
			} else if ($message['action']['_'] === 'messageActionChatJoinedByLink') {
				/**
				 * Downloading the user's informations from the Combot Anti-Spam API
				 *
				 * json_decode() convert a JSON string into a PHP variables
				 */
				$result = yield $this -> getHttpClient() -> request(new Amp\Http\Client\Request('https://api.cas.chat/check?user_id=' . $message['from_id']));

				// Retrieving the result
				$result = yield $result -> getBody() -> buffer();

				$result = json_decode($result, TRUE);

				// Retrieving the data of the new member
				$new_member = $this -> getInfos($message['from_id']);

				/**
				 * Checking if the new member is empty
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
				if (empty($new_member)) {
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
				// Checking if the user is a spammer or is a deleted account
				} else if ($result['ok'] || $new_member['scam'] || $new_member['deleted']) {
					yield $this -> channels -> editBanned([
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
					]);
				}

				// Insert the data of the user
				$this -> insertChatsData($update['chat_id'], $new_member['id']);

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
				if (empty($answer) === FALSE) {
					/**
					 * Personalizing the message
					 *
					 * str_replace() replace the 'mentions' tag with the string
					 */
					$answer = str_replace('${mentions}', '<a href=\"mention:' . $new_member['id'] . '\" >' . $new_member['first_name'] . '</a>', $answer);

					$welcome = yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $update['chat_id'],
						'message' => $answer,
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);

					$welcome = $welcome['updates'][0]['message'];

					// Opening a transaction
					$transaction = yield $this -> DB -> beginTransaction();

					// Insert the message
					try {
						yield $transaction -> execute('INSERT INTO `Messages_to_delete` (`id`, `ttl`) VALUES (?, ?);', [
							$welcome['id'],
							/**
							 * Retrieving the TTL (5 minutes in the future)
							 *
							 * date() return the actual datetime with the given format
							 * mktime() create the datetime
							 */
							date('Y-m-d H:i:s', mktime(date('G'), date('i') + 5))
						]);
					} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					}

					// Commit the change
					yield $transaction -> commit();

					// Closing the transaction
					yield $transaction -> close();
				}
			} else if ($message['action']['_'] === 'messageActionChatMigrateTo') {
				// Opening a transaction
				$transaction = yield $this -> DB -> beginTransaction();

				// Updating the chat's data
				try {
					yield $transaction -> execute('UPDATE `Chats` SET `id`=? WHERE `id`=?;', [
						$message['action']['channel_id'],
						$chat['id']
					]);
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

					// Closing the transaction
					yield $transaction -> close();
					return;
				}

				// Commit the change
				yield $transaction -> commit();

				// Closing the transaction
				yield $transaction -> close();
			} else if ($message['action']['_'] === 'messageActionChannelMigrateFrom') {
				// Opening a transaction
				$transaction = yield $this -> DB -> beginTransaction();

				// Updating the chat's data
				try {
					yield $transaction -> execute('UPDATE `Chats` SET `id`=? WHERE `id`=?;', [
						$chat['id'],
						$message['action']['chat_id']
					]);
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

					// Closing the transaction
					yield $transaction -> close();
					return;
				}

				// Commit the change
				yield $transaction -> commit();

				// Closing the transaction
				yield $transaction -> close();
			} else if ($message['action']['_'] === 'messageActionChatDeleteUser') {
				// Opening a transaction
				$transaction = yield $this -> DB -> beginTransaction();

				try {
					yield $transaction -> execute('UPDATE `Chats_data` SET `ttl`=? WHERE `user_id`=? AND `chat_id`=?;', [
						/**
						 * Retrieving the TTL (15 days in the future)
						 *
						 * date() return the actual datetime with the given format
						 * mktime() create the datetime
						 */
						date('Y-m-d H:i:s', mktime(date('G'), date('i'), date('s'), date('n'), date('j') + 15)),
						$message['action']['user_id'],
						$chat['id']
					]);
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
					return;
				}

				// Commit the change
				yield $transaction -> commit();

				// Closing the transaction
				yield $transaction -> close();
			}

			yield $this -> channels -> deleteMessages([
				'revoke' => TRUE,
				'id' => [
					$message['id']
				]
			]);
			return;
		}

		// Checking if the chat is an allowed chat
		try {
			yield $this -> DB -> execute('SELECT NULL FROM `Chats` WHERE `id`=?;', [
				$chat['id']
			]);
		} catch (Amp\Sql\QueryError $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			return;
		} catch (Amp\Sql\FailureException $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

			// Checking if the chat is a (super)group or a channel
			if ($chat['type'] !== 'user' && $chat['type'] !== 'bot') {
				$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from an unauthorized chat.');

				// Leaving the chat
				if ($chat['type'] === 'chat') {
					$this = yield $this -> getSelf();

					$this -> messages -> deleteChatUser([
						'chat_id' => $chat['id'],
						'user_id' => $this['id']
					]);
				} else {
					$this -> channels -> leaveChannel([
						'channel' => $chat['id']
					]);
				}

				$this -> logger('The bot have lefted the unauthorized chat.');
			}
			return;
		}

		// Checking if the chat isn't a (super)group or a private chat
		if ($chat['type'] === 'bot' || $chat['type'] === 'channel') {
			$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from a bot or a channel.');
			return;
		}

		/**
		 * Encode the text
		 *
		 * trim() strip whitespaces from the begin and the end of the string
		 * htmlentities() convert all HTML character to its safe value
		 */
		$message['message'] = trim($message['message']);
		$message['message'] = htmlentities($message['message'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML5, 'UTF-8');

		// Checking if the user is in the bot's blacklist
		try {
			$result = yield $this -> DB -> execute('SELECT NULL FROM `Blacklist` WHERE `id`=?;', [
				$sender['id']
			]);
		} catch (Amp\Sql\QueryError $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			return;
		} catch (Amp\Sql\FailureException $e) {
		}

		// Checking if the query has product a result
		if ($result instanceof Amp\Mysql\ResultSet) {
			$this -> logger('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> tried to use the bot.');
			return;
		}

		// Retrieving the language of the user
		$language = $this -> getLanguage($sender['lang_code']);

		// Checking if the user starts the bot for the first time
		try {
			$result = yield $this -> DB -> execute('SELECT  * FROM `Users` WHERE `id`=?;', [
				$sender['id']
			]);
		} catch (Amp\Sql\QueryError $e) {
			$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			return;
		} catch (Amp\Sql\FailureException $e) {
			// Opening a transaction
			$transaction = yield $this -> DB -> beginTransaction();

			// Insert the user
			try {
				yield $transaction -> execute('INSERT INTO `Users` (`id`, `first_name`, `last_name`, `lang_code`) VALUES (?, ?, ?, ?);', [
					$sender['id'],
					$sender['first_name'],
					$sender['last_name'],
					$language
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				return;
			}

			// Commit the change
			yield $transaction -> commit();

			// Closing the transaction
			yield $transaction -> close();
		}

		// Checking if the query has product a result
		if ($result instanceof Amp\Mysql\ResultSet) {
			// Opening a transaction
			$transaction = yield $this -> DB -> beginTransaction();

			// Insert the user
			try {
				yield $transaction -> execute('UPDATE `Users` SET `last_use`=? WHERE `id`=?;', [
					/**
					 * Retrieving the actual datetime
					 *
					 * date() return the actual datetime with the given format
					 */
					date('Y-m-d H:i:s'),
					$sender['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				return;
			}

			// Commit the change
			yield $transaction -> commit();

			// Closing the transaction
			yield $transaction -> close();
		}

		/**
		 * Checking if is an @admin tag
		 *
		 * preg_match() perform a RegEx match
		 */
		if (preg_match('/^@admin([[:blank:]\n]((\n|.) *))?$/miu', $message['message'], $matches)) {
			// Checking if the chat is a private chat
			if ($chat['type'] === 'user') {
				$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from a private chat (@admin section).');
				return;
			}

			/**
			 * Retrieving the admins list
			 *
			 * array_filter() filters the array by the role of each member
			 * array_map() convert each admins to its id
			 */
			$admins = array_filter($chat['participants'], function ($n) {
				return $n['role'] === 'admin' || $n['role'] === 'creator';
			});
			$admins = array_map(function ($n) {
				return $n['user'];
			}, $admins);

			// Retrieving the admin message
			$answer = $this -> getOutputMessage($language, 'admin_message', '<a href=\"mention:${admin_id}\" >${admin_first_name}</a>,\n<a href=\"mention:${sender_id}\" >${sender_first_name}</a> needs your help${motive} into <a href=\"${chat_invite}\" >${chat_title}</a>.');

			/**
			 * Personalizing the admin message
			 *
			 * str_replace() replace the tags with their value
			 */
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
					'clear_draft' => TRUE,
					'parse_mode' => 'HTML'
				];
			}

			yield $this -> messages -> sendMessage($message);

			// Sending the report to the channel
			$this -> logger('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> has sent an @admin request into <a href=\"' . $chat['invite'] . '\" >' . $chat['title'] . '</a>.');
			$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> has sent an @admin request into <a href=\"' . $chat['invite'] . '\" >' . $chat['title'] . '</a>.');
		/**
		 * Checking if the message contains a Whatsapp link
		 *
		 * preg_match() perform a RegEx match
		 */
		} else if (preg_match('/^. *(https?:\/\/)?chat\.whatsapp\.com\/?. *$/miu', $message['message'])) {
			yield $this -> channels -> deleteMessages([
				'revoke' => TRUE,
				'id' => [
					$message['id']
				]
			]);
		/**
		 * Checking if is a bot command
		 *
		 * preg_match() perform a RegEx match
		 */
		} else if (preg_match('/^\/([[:alnum:]@]+)[[:blank:]]?([[:alnum:]]|[^\n]+)?$/miu', $message['message'], $matches)) {
			/**
			 * Retrieving the command
			 *
			 * explode() convert a string into an array
			 */
			$command = explode('@', $matches[1])[0];
			$args = $matches[2] ?? NULL;

			switch ($command) {
				case 'add':
				case 'remove':
					// Checking if the sender is a bot's admin
					try {
						yield $this -> DB -> execute('SELECT NULL FROM `Admins` WHERE `id`=?;', [
							$sender['id']
						]);
					} catch (Amp\Sql\QueryError $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					} catch (Amp\Sql\FailureException $e) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from an unauthorized user (/' . $command . ' section).');
						return;
					}

					// Checking if the chat is a private chat
					if ($chat['type'] === 'user') {
						// Checking if is an add request
						if ($command === 'add') {
							// Retrieving the add_lang message
							$answer = $this -> getOutputMessage($language, 'add_lang_message', 'Send me a message with this format:' . "\n\n" . '<code>lang_code: &lt;insert here the lang_code of the language&gt;' . "\n" . 'add_lang_message: &lt;insert here the message for the /add command when a user want add a language&gt;' . "\n" . 'admin_message: &lt;insert here the message for the @admin tag&gt;' . "\n" . 'confirm_message: &lt;insert here a generic confirm message&gt;' . "\n" . 'help_message: &lt;insert here the message for the /help command&gt;' . "\n" . 'invalid_parameter_message: &lt;insert here the message that will be sent when a user insert an invalid parameter into a command&gt;' . "\n" . 'invalid_syntax_message: &lt;insert here the message that will be sent when a user send a command with an invalid syntax&gt;' . "\n" . 'mute_message: &lt;insert here the message for the /mute command&gt;' . "\n" . 'mute_advert_message: &lt;insert here the message for when the /mute command is used with time set to forever&gt;' . "\n" . 'link_message: &lt;insert here the message for the /link command&gt;' . "\n" . 'reject_message: &lt;insert here a generic reject message&gt;' . "\n" . 'staff_group_message: &lt;insert here the message for the /staff_group command&gt;' . "\n" . 'start_message: &lt;insert here the message for the /start command&gt;' . "\n" . 'unknown_message: &lt;insert here the message for the unknown commands&gt;' . "\n" . 'update_message: &lt;insert here the message for the /update command&gt;</code>' . "\n\n" . '<b>N.B.</b>: If you want insert a new line in the messages, you must codify it as <code>\n</code>.');

							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $sender['id'],
								'message' => $answer,
								'reply_to_msg_id' => $message['id'],
								'clear_draft' => TRUE,
								'parse_mode' => 'HTML'
							]);
						} else {
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
							if (empty($args) === FALSE) {
								// Checking if the language is into the database
								try {
									yield $this -> DB -> execute('SELECT NULL FROM `Languages` WHERE `lang_code`=?;', [
										$args
									]);
								} catch (Amp\Sql\QueryError $e) {
									$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
									return;
								} catch (Amp\Sql\FailureException $e) {
									// Retrieving the invalid_parameter message
									$answer = $this -> getOutputMessage($language, 'invalid_parameter_message', 'The ${parameter} is invalid.');

									/**
									 * Personalizing the invalid_parameter message
									 *
									 * str_replace() replace the tags with their value
									 */
									$answer = str_replace('${parameter}', 'lang_code', $answer);

									yield $this -> messages -> sendMessage([
										'no_webpage' => TRUE,
										'peer' => $sender['id'],
										'message' => $answer,
										'reply_to_msg_id' => $message['id'],
										'clear_draft' => TRUE,
										'parse_mode' => 'HTML'
									]);

									$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the command have a wrong syntax (/' . $command . ' section).');
									return;
								}

								// Opening a transaction
								$transaction = yield $this -> DB -> beginTransaction();

								// Removing the language
								try {
									yield $transaction -> execute('DELETE FROM `Languages` WHERE `lang_code`=?;', [
										$args
									]);
								} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
									$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
									return;
								}

								// Commit the change
								yield $transaction -> commit();

								// Closing the transaction
								yield $transaction -> close();

								// Retrieving the confirm message
								$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

								yield $this -> messages -> sendMessage([
									'no_webpage' => TRUE,
									'peer' => $sender['id'],
									'message' => $answer,
									'reply_to_msg_id' => $message['id'],
									'clear_draft' => TRUE,
									'parse_mode' => 'HTML'
								]);
							} else {
								// Retrieving the invalid_syntax message
								$answer = $this -> getOutputMessage($language, 'invalid_syntax_message', 'The syntax of the command is: <code>${syntax}</code>.');

								/**
								 * Personalizing the invalid_syntax message
								 *
								 * str_replace() replace the tags with their value
								 */
								$answer = str_replace('${syntax}', '/' . $command . ' &lt;lang_code&gt;', $answer),

								yield $this -> messages -> sendMessage([
									'no_webpage' => TRUE,
									'peer' => $sender['id'],
									'message' => $answer,
									'reply_to_msg_id' => $message['id'],
									'clear_draft' => TRUE,
									'parse_mode' => 'HTML'
								]);

								$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the command have a wrong syntax (/' . $command . ' section).');
							}
						}
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
					if (empty($message['reply_to_msg_id'])) {
						// Retrieving the query
						$sql_query = $command === 'add' ? 'INSERT INTO `Chats` (`id`, `type`, `title`, `username`, `invite_link`, `permissions`) VALUES (?, ?, ?, ?, ?, ?);' : 'DELETE FROM `Chats` WHERE `id`=?;';

						// Retrieving the default permissions of the chat
						$permissions = $this -> getInfos($chat['id']);

						/**
						 * Checking if the chat is empty
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
						if (empty($permissions)) {
							$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the retrieve process of the default permissions of the chat failed (/' . $command . ' section).');
							return;
						}

						$bitmask = bitmask($permissions['default_banned_rights']);

						// Opening a transaction
						$transaction = yield $this -> DB -> beginTransaction();

						try {
							yield $transaction -> execute($sql_query, $command === 'add' ? [
								$chat['id'],
								$chat['type'],
								$chat['title'],
								$chat['username'],
								$chat['invite'],
								$bitmask
							] : [
								$chat['id']
							]);
						} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
							$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Commit the change
						yield $transaction -> commit();

						// Closing the transaction
						yield $transaction -> close();

						// Retrieving the confirm message
						$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $chat['id'],
							'message' => $answer,
							'reply_to_msg_id' => $message['id'],
							'clear_draft' => TRUE,
							'parse_mode' => 'HTML'
						]);

						// Sending the report to the channel
						$this -> logger('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . ($command == 'add' ? 'e' : '') . 'd <a href=\"' . $chat['invite'] . '\" >' . $chat['title'] . '</a> ' . ($command == 'add' ? 'into' : 'from') . ' the database.');
						$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . ($command == 'add' ? 'e' : '') . 'd <a href=\"' . $chat['invite'] . '\" >' . $chat['title'] . '</a> ' . ($command == 'add' ? 'into' : 'from') . ' the database.');
						return;
					}

					// Retrieving the message this message replies to
					$reply_message = $this -> getMessages([
						$message['reply_to_msg_id']
					]);

					/**
					 * Checking if the message is empty
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
					if (empty($reply_message)) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because wasn&apos;t a message that replies to another message (/' . $command . ' section).');
						return;
					}

					$reply_message = $reply_message[0];

					// Retrieving the data of the user
					$user = $this -> getInfos($reply_message['from_id']);

					/**
					 * Checking if the user is empty
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
					if (empty($user)) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the user of the reply_message isn&apos;t a normal user (/' . $command . ' section).');
						return;
					}

					// Retrieving the query
					$sql_query = $command === 'add' ? 'INSERT INTO `Admins` (`id`, `added_by`) VALUES (?, ?);' : 'DELETE FROM `Admins` WHERE `id`=?;';

					// Opening a transaction
					$transaction = yield $this -> DB -> beginTransaction();

					try {
						yield $transaction -> execute($sql_query, $command === 'add' ? [
							$user['id'],
							$sender['id']
						] : [
							$user['id']
						]);
					} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					}

					// Commit the change
					yield $transaction -> commit();

					// Closing the transaction
					yield $transaction -> close();

					/**
					 * @todo Complete the function with code that makes the user admin in all groups in common with the bot.
					 */

					// Retrieving the confirm message
					$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $chat['id'],
						'message' => $answer,
						'reply_to_msg_id' => $message['id'],
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);

					// Sending the report to the channel
					$this -> logger('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command == 'add' ? 'assigned' : 'removed' . ' <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a> as bot&apos;s admin.');
					$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command == 'add' ? 'assigned' : 'removed' . ' <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a> as bot&apos;s admin.');
					break;
				case 'administer':
					// Checking if the chat is a private chat
					if ($chat['type'] === 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from a private chat (/' . $command . ' section).');
						return;
					}

					try {
						$result = yield $transaction -> execute('SELECT `to_administer` FROM `Chats` WHERE `id`=?;', [
							$chat['id']
						]);
					} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					}

					yield $result -> advance();
					$result = $result -> getCurrent();
					$result = bool($result['to_administer']);
					$result = ~ $result;

					// Opening a transaction
					$transaction = yield $this -> DB -> beginTransaction();

					// Commuting the 'to_administer' flag of the chat
					try {
						yield $transaction -> execute('UPDATE `Chats` SET `to_administer`=? WHERE `id`=?;', [
							$result,
							$chat['id']
						]);
					} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

						// Closing the transaction
						yield $transaction -> close();
						return;
					}

					// Commit the change
					yield $transaction -> commit();

					// Closing the transaction
					yield $transaction -> close();

					// Retrieving the confirm message
					$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $chat['id'],
						'message' => $answer,
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);
					break;
				case 'announce':
					// Checking if the chat is a private chat
					if ($chat['type'] === 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from a private chat (/' . $command . ' section).');
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
					if (empty($args) === FALSE) {
						// Checking if is a serious use of the /announce command (command runned into the staff group)
						try {
							$result = yield $this -> DB -> execute('SELECT `id` FROM `Chats` WHERE `staff_group`=?;', [
								$chat['id']
							]);
						} catch (Amp\Sql\QueryError $e) {
							$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
							return;
						} catch (Amp\Sql\FailureException $e) {
							/**
							 * Retrieving the admins list
							 *
							 * array_filter() filters the array by the role of each member
							 * array_map() convert each admins to its id
							 */
							$admins = array_filter($chat['participants'], function ($n) {
								return $n['role'] === 'admin' || $n['role'] === 'creator';
							});
							$admins = array_map(function ($n) {
								return $n['user']['id'];
							}, $admins);

							/**
							 * Checking if the user is an admin
							 *
							 * in_array() check if the array contains an item that match the element
							 */
							if (in_array($sender['id'], $admins) === FALSE) {
								$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the sender isn&apos;t an admin of the chat (/' . $command . ' section).');
								return;
							}

							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $chat['id'],
								'message' => $args,
								'clear_draft' => TRUE,
								'parse_mode' => 'HTML'
							]);
							return;
						}

						// Checking if is a serious use of the /announce command (command runned by a bot's admin)
						try {
							yield $this -> DB -> execute('SELECT NULL FROM `Admins` WHERE `id`=?;', [
								$sender['id']
							]);
						} catch (Amp\Sql\QueryError $e) {
							$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
							return;
						} catch (Amp\Sql\FailureException $e) {
							$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the user that sent it wasn&apos;t a bot\'s admin (/' . $command . ' section).');
							return;
						}

						$messages = [
							'multiple' => TRUE
						];

						$chats = [];

						// Cycle on the result
						while (yield $result -> advance()) {
							$chats []= $result -> getCurrent();
						}

						// Cycle on the chats that have this staff group
						foreach ($chats as $sub_chat) {
							$messages []= [
								'no_webpage' => TRUE,
								'peer' => $sub_chat['id'],
								'message' => $args,
								'clear_draft' => TRUE,
								'parse_mode' => 'HTML'
							];
						}

						yield $this -> messages -> sendMessage($messages);
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
					if ($chat['type'] === 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from a private chat (/' . $command . ' section).');
						return;
					}

					// Checking if is a serious use of the commands (command runned in the staff group)
					try {
						$result = yield $this -> DB -> execute('SELECT `id` FROM `Chats` WHERE `staff_group`=?;', [
							$chat['id']
						]);
					} catch (Amp\Sql\QueryError $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					} catch (Amp\Sql\FailureException $e) {
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
						if (empty($message['reply_to_msg_id'])) {
							$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because wasn&apos;t a message that replies to another message (/' . $command . ' section).');
							return;
						}

						// Setting limit to forever
						$limit = 0;

						/**
						 * Checking if the command is /mute, if it has arguments and if the arguments are correct
						 *
						 * preg_match() perform a RegEx match
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
						if ($command == 'mute' && empty($args) === FALSE && preg_match('/^([[:digit:]]+)[[:blank:]]?([[:alpha:]]+)?$/miu', $args, $matches)) {
							$limit = $matches[1];

							/**
							 * Checking if the time option is already expressed in seconds
							 *
							 * preg_match() perform a RegEx match
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
							if (empty($matches[2]) === FALSE) {
								// Converting the units to seconds
								switch ($matches[2]) {
									case 'm':
									case 'min':
									case 'minuto':
									case 'minute':
									case 'minuti':
									case 'minutes':
										$limit  *= 60;
										break;
									case 'h':
									case 'ora':
									case 'hour':
									case 'ore':
									case 'hours':
										$limit  *= 60  * 60;
										break;
									case 'g':
									case 'd':
									case 'giorno':
									case 'day':
									case 'giorni':
									case 'days':
										$limit  *= 60  * 60  * 24;
										break;
									case 'M':
									case 'mese':
									case 'month':
									case 'mesi':
									case 'months':
										$limit  *= 60  * 60  * 24  * 30;
									case 'a':
									case 'y':
									case 'anno':
									case 'year':
										$limit  *= 60  * 60  * 24  * 30  * 12;
										break;
									default:
										// Retrieving the mute message
										$answer = $this -> getOutputMessage($language, 'mute_message', 'The syntax of the command is: <code>/mute [time]</code>.' . "\n" . 'The <code>time</code> option must be more then 30 seconds and less of 366 days.' . "\n" . 'If you want, you can use the short syntax for the unit time.');

										yield $this -> messages -> sendMessage([
											'no_webpage' => TRUE,
											'peer' => $chat['id'],
											'message' => $answer,
											'reply_to_msg_id' => $message['id'],
											'clear_draft' => TRUE,
											'parse_mode' => 'HTML'
										]);
										return;
								}
							}
						}

						// Retrieving the message this message replies to
						$reply_message = $this -> getMessages([
							$message['reply_to_msg_id']
						]);

						/**
						 * Checking if the message is empty
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
						if (empty($reply_message)) {
							$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because wasn&apos;t a message that replies to another message (/' . $command . ' section).');
							return;
						}

						$reply_message = $reply_message[0];

						// Retrieving the data of the user
						$user = $this -> getInfos($reply_message['from_id']);

						/**
						 * Checking if the user is empty
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
						if (empty($user)) {
							$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the user of the reply_message isn&apos;t a normal user (/' . $command . ' section).');
							return;
						}

						// Retrieving the default permissions of the chat
						$permissions = $this -> getInfos($chat['id']);

						/**
						 * Checking if the chat is empty
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
						if (empty($permissions)) {
							$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the retrieve process of the default permissions of the chat failed (/' . $command . ' section).');
							return;
						}

						$permissions = $permissions['default_banned_rights'];

						// Checking if the command is one of: /ban, /kick or /mute
						if ($command === 'ban' || $command === 'kick' || $command === 'mute') {
							yield $this -> channels -> editBanned([
								'channel' => $chat['id'],
								'user_id' => $reply_message['from_id'],
								'banned_rights' => [
									'_' => 'chatBannedRights',
									'view_messages' => $command === 'mute' ? $permissions['view_messages'] : TRUE,
									'send_messages' => TRUE,
									'send_media' => TRUE,
									'send_stickers' => TRUE,
									'send_gifs' => TRUE,
									'send_games' => TRUE,
									'send_inline' => TRUE,
									'embed_links' => $command === 'mute' ? $permissions['embed_links'] : TRUE,
									'send_polls' => TRUE,
									'change_info' => $command === 'mute' ? $permissions['change_info'] : TRUE,
									'invite_users' => $command === 'mute' ? $permissions['invite_users'] : TRUE,
									'pin_messages' => $command === 'mute' ? $permissions['pin_messages'] : TRUE,
									'until_date' => $limit
								]
							]);

							// Checking if the command is one of: /ban or /mute
							if ($command !== 'kick') {
								// Opening a transaction
								$transaction = yield $this -> DB -> beginTransaction();

								// Insert the penalty
								try {
									yield $transaction -> execute('INSERT INTO `Penalty` (`chat_id`, `user_id`, `type`, `id`, `execute_by`) VALUES (?, ?, ?, \"1\", ?);', [
										$chat['id'],
										$reply_message['from_id'],
										$command,
										$sender['id']
									]);
								} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
									$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
									return;
								}

								// Commit the change
								yield $transaction -> commit();

								// Closing the transaction
								yield $transaction -> close();
							}
						}

						// Checking if the command is one of: /kick, /unban or /unmute
						if ($command === 'kick' || $command === 'unban' || $command === 'unmute') {
							yield $this -> channels -> editBanned([
								'channel' => $chat['id'],
								'user_id' => $reply_message['from_id'],
								'banned_rights' => $permissions
							]);

							// Checking if the command is one of: /unban or /unmute
							if ($command !== 'kick') {
								// Opening a transaction
								$transaction = yield $this -> DB -> beginTransaction();

								// Removing the penalty
								try {
									yield $transaction -> execute('DELETE FROM `Penalty` WHERE `chat_id`=? AND `user_id`=? AND `type`=? AND `id`=\"1\";', [
										$chat['id'],
										$reply_message['from_id'],
										$command
									]);
								} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
									$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
									return;
								}

								// Commit the change
								yield $transaction -> commit();

								// Closing the transaction
								yield $transaction -> close();
							}
						}

						/**
						 * Checking if is a /(un)silence command
						 *
						 * preg_match() perform a RegEx match
						 */
						if (preg_match('/^(un)?silence/miu', $command)) {
							// Retrieving the default permissions of the chat
							try {
								$permissions = $this -> DB -> execute('SELECT `permissions` FROM `Chats` WHERE `id`=?;', [
									$chat['id']
								]);
							} catch (Amp\Sql\QueryError $e) {
								$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
								return;
							} catch (Amp\Sql\FailureException $e) {
								$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because there was a problem into the retrieve process of the permissions of the chat ' . $chat['id'] . ' (/' . $command . ' section).');
								return;
							}

							// Checking if the query has product a result
							if ($permissions instanceof Amp\Mysql\ResultSet) {
								$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because there was a problem into the retrieve process of the permissions of the chat ' . $chat['id'] . ' (/' . $command . ' section).');
								return;
							}

							yield $permissions -> advance();
							$permissions = $permissions -> getCurrent();
							$permissions = $permissions['permissions'];

							yield $this -> messages -> editChatDefaultBannedRights([
								'peer' => $chat['id'],
								'banned_rights' => [
									'_' => 'chatBannedRights',
									'view_messages' => FALSE,
									'send_messages' => $command === 'unsilence' ? $permissions & 1 << 10 : TRUE,
									'send_media' => $command === 'unsilence' ? $permissions & 1 << 9 : TRUE,
									'send_stickers' => $command === 'unsilence' ? $permissions & 1 << 8 : TRUE,
									'send_gifs' => $command === 'unsilence' ? $permissions & 1 << 7 : TRUE,
									'send_games' => $command === 'unsilence' ? $permissions & 1 << 6 : TRUE,
									'send_inline' => $command === 'unsilence' ? $permissions & 1 << 5 : TRUE,
									'embed_links' => $command === 'unsilence' ? $permissions & 1 << 4 : TRUE,
									'send_polls' => $command === 'unsilence' ? $permissions & 1 << 3 : TRUE,
									'change_info' => $command === 'unsilence' ? $permissions & 1 << 2 : TRUE,
									'invite_users' => $command === 'unsilence' ? $permissions & 1 << 4 : TRUE,
									'pin_messages' => $command === 'unsilence' ? $permissions & 1 << 0 : TRUE,
									'until_date' => 0
								]
							]);
						}

						// Checking if is a permanent /mute command
						if ($command === 'mute' && ($limit < 30 || $limit > 60  * 60  * 24  * 30  * 12)) {
							// Retrieving the mute_advert message
							$answer = $this -> getOutputMessage($language, 'mute_advert_message', 'You have muted <a href=\"mention:${user_id}\" >${user_first_name}</a> forever.');

							/**
							 * Personalizing the message
							 *
							 * str_replace() replace the tags with their value
							 */
							$answer = str_replace('${user_first_name}', $user['first_name'], $answer);
							$answer = str_replace('${user_id}', $reply_message['from_id'], $answer);

							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $chat['id'],
								'message' => $answer,
								'reply_to_msg_id' => $message['id'],
								'clear_draft' => TRUE,
								'parse_mode' => 'HTML'
							]);
						}

						// Setting the verb of the report
						$verb = $command;

						/**
						 * Checking if is a /(un)ban command
						 *
						 * preg_match() perform a RegEx match
						 */
						if (preg_match('/^(un)?ban/miu', $command)) {
							$verb .= 'ne';
						// Checking if is a /kick command
						} else if ($command === 'kick') {
							$verb .= 'e';
						}

						$verb .= 'd';

						// Sending the report to the channel
						$this -> logger('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $verb . ' <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a>' . ($command == 'mute' && $limit > 30 && $limit < 60  * 60  * 24  * 366 ? ' for ' . $args : '') . '.');
						$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $verb . ' <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a>' . ($command == 'mute' && $limit > 30 && $limit < 60  * 60  * 24  * 366 ? ' for ' . $args : '') . '.');
						return;
					}

					// Checking if is a serious use of the commands (command runned by a bot's admin)
					try {
						yield $this -> DB -> execute('SELECT NULL FROM `Admins` WHERE `id`=?;', [
							$sender['id']
						]);
					} catch (Amp\Sql\QueryError $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					} catch (Amp\Sql\FailureException $e) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because isn&apos;t a serious use (/' . $command . ' section).');
						return;
					}

					$chats = [];

					// Cycle on the result
					while (yield $result -> advance()) {
						$sub_chat = $result -> getCurrent();

						// Retrieving the data of the chat
						$sub_chat = $this -> getInfos($sub_chat['id']);

						/**
						 * Checking if the chat is empty
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
						if (empty($sub_chat)) {
							continue;
						}

						$chats []= $sub_chat;
					}

					/**
					 * Checking if the command is a /(un)ban command
					 *
					 * preg_match() perform a RegEx match
					 */
					if (preg_match('/^(un)?ban/miu', $command)) {
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
						if (empty($args)) {
							// Retrieving the invalid_syntax message
							$answer = $this -> getOutputMessage($language, 'invalid_syntax_message', 'The syntax of the command is: <code>${syntax}</code>.');

							/**
							 * Personalizing the message
							 *
							 * str_replace() replace the tag with its value
							 */
							$answer = str_replace('${syntax}', '/' . $command . ' &lt;user_id|username&gt;', $answer);

							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $sender['id'],
								'message' => $answer,
								'reply_to_msg_id' => $message['id'],
								'clear_draft' => TRUE,
								'parse_mode' => 'HTML'
							]);

							$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the command have a wrong syntax (/' . $command . ' section).');
							return;
						}

						// Retrieving the data of the user
						$user = $this -> getInfos($args);

						/**
						 * Checking if the user is empty
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
						if (empty($user)) {
							// Retrieving the invalid_parameter message
							$answer = $this -> getOutputMessage($language, 'invalid_parameter_message', 'The ${parameter} is invalid.');

							/**
							 * Personalizing the message
							 *
							 * str_replace() replace the tag with its value
							 */
							$answer = str_replace('${parameter}', 'username/id', $answer);

							yield $this -> messages -> sendMessage([
								'no_webpage' => TRUE,
								'peer' => $sender['id'],
								'message' => $answer,
								'reply_to_msg_id' => $message['id'],
								'clear_draft' => TRUE,
								'parse_mode' => 'HTML'
							]);

							$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the command have a wrong syntax (/' . $command . ' section).');
							return;
						}

						// Cycle on the chats that have this staff group
						foreach ($chats as $sub_chat) {
							yield $this -> channels -> editBanned([
								'channel' => $sub_chat['id'],
								'user_id' => $user['id'],
								'banned_rights' => $command === 'unban' ? $sub_chat['default_banned_rights'] : [
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
						$this -> logger('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . 'ned <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a> from all chats.');
						$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . 'ned <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a> from all chats.');
					/**
					 * Checking if the command is a /(un)silence command
					 *
					 * preg_match() perform a RegEx match
					 */
					} else if (preg_match('/^(un)?silence/miu', $command)) {
						// Cycle on the chats that have this staff group
						foreach ($chats as $sub_chat) {
							// Retrieving the default permissions of the chat
							try {
								$permissions = $this -> DB -> execute('SELECT `permissions` FROM `Chats` WHERE `id`=?;', [
									$sub_chat['id']
								]);
							} catch (Amp\Sql\QueryError $e) {
								$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
								continue;
							} catch (Amp\Sql\FailureException $e) {
								$this -> logger('The /' . $command . ' command wasn&apos;t completed because there was a problem into the retrieve process of the permissions of the chat ' . $sub_chat['id'] . '.');
								continue;
							}

							// Checking if the query has product a result
							if ($permissions instanceof Amp\Mysql\ResultSet) {
								$this -> logger('The /' . $command . ' command wasn&apos;t completed because there was a problem into the retrieve process of the permissions of the chat ' . $sub_chat['id'] . '.');
								continue;
							}

							yield $permissions -> advance();
							$permissions = $permissions -> getCurrent();
							$permissions = $permissions['permissions'];

							yield $this -> messages -> editChatDefaultBannedRights([
								'peer' => $sub_chat['id'],
								'banned_rights' => [
									'_' => 'chatBannedRights',
									'view_messages' => FALSE,
									'send_messages' => $command === 'unsilence' ? $permissions & 1 << 10 : TRUE,
									'send_media' => $command === 'unsilence' ? $permissions & 1 << 9 : TRUE,
									'send_stickers' => $command === 'unsilence' ? $permissions & 1 << 8 : TRUE,
									'send_gifs' => $command === 'unsilence' ? $permissions & 1 << 7 : TRUE,
									'send_games' => $command === 'unsilence' ? $permissions & 1 << 6 : TRUE,
									'send_inline' => $command === 'unsilence' ? $permissions & 1 << 5 : TRUE,
									'embed_links' => $command === 'unsilence' ? $permissions & 1 << 4 : TRUE,
									'send_polls' => $command === 'unsilence' ? $permissions & 1 << 3 : TRUE,
									'change_info' => $command === 'unsilence' ? $permissions & 1 << 2 : TRUE,
									'invite_users' => $command === 'unsilence' ? $permissions & 1 << 4 : TRUE,
									'pin_messages' => $command === 'unsilence' ? $permissions & 1 << 0 : TRUE,
									'until_date' => 0
								]
							]);
						}

						// Sending the report to the channel
						$this -> logger('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . 'd all the chats.');
						$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . 'd all the chats.');
					}
					break;
				case 'blacklist':
				case 'unblacklist':
					// Checking if the chat is a private chat
					if ($chat['type'] === 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from a private chat (/' . $command . ' section).');
						return;
					}

					// Checking if the sender is a bot's admin
					try {
						yield $this -> DB -> execute('SELECT NULL FROM `Admins` WHERE `id`=?;', [
							$sender['id']
						]);
					} catch (Amp\Sql\QueryError $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					} catch (Amp\Sql\FailureException $e) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from an unauthorized user (/' . $command . ' section).');
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
					if (empty($message['reply_to_msg_id'])) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because wasn&apos;t a message that replies to another message (/' . $command . ' section).');
						return;
					}

					// Retrieving the message this message replies to
					$reply_message = $this -> getMessages([
						$message['reply_to_msg_id']
					]);

					/**
					 * Checking if the message is empty
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
					if (empty($reply_message)) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because wasn&apos;t a message that replies to another message (/' . $command . ' section).');
						return;
					}

					$reply_message = $reply_message[0];

					// Retrieving the data of the user
					$user = $this -> getInfos($reply_message['from_id']);

					/**
					 * Checking if the user is empty
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
					if (empty($user)) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the user of the reply_message isn&apos;t a normal user (/' . $command . ' section).');
						return;
					}

					$sql_query = $command === 'blacklist' ? 'INSERT INTO `Blacklist` (`id`, `banned_by`) VALUES (?, ?);' : 'DELETE FROM `Blacklist` WHERE `id`=?;';

					// Opening a transaction
					$transaction = yield $this -> DB -> beginTransaction();

					// Insert the user into the blacklist
					try {
						yield $transaction -> execute($sql_query, $command === 'blacklist' ? [
							$user['id'],
							$sender['id']
						] : [
							$user['id']
						]);
					} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
						// Closing the transaction
						yield $transaction -> close();

						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					}

					// Commit the change
					yield $transaction -> commit();

					// Closing the transaction
					yield $transaction -> close();

					// Retrieving the confirm message
					$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $sender['id'],
						'message' => $answer,
						'reply_to_msg_id' => $message['id'],
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);

					// Sending the report to the channel
					$this -> logger('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . 'ed <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a>.');
					$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> ' . $command . 'ed <a href=\"mention:' . $user['id'] . '\" >' . $user['first_name'] . '</a>.');
					break;
				case 'export_invite_link':
					// Checking if the chat is a private chat
					if ($chat['type'] === 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from a private chat (/' . $command . ' section).');
						return;
					}

					$result = yield $this -> messages -> exportChatInvite([
						'peer' => $chat['id']
					]);

					// Checking if the method has product a result
					if ($result['_'] === 'chatInviteEmpty') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the retrieve process of the new invite link of the chat failed (/' . $command . ' section).');
						return;
					}

					// Opening a transaction
					$transaction = yield $this -> DB -> beginTransaction();

					// Updating the invite link
					try {
						yield $transaction -> execute('UPDATE `Chats` SET `invite_link`=? WHERE `id`=?;', [
							$result['link'],
							$chat['id']
						]);
					} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

						// Closing the transaction
						yield $transaction -> close();
						return;
					}

					// Commit the change
					yield $transaction -> commit();

					// Closing the transaction
					yield $transaction -> close();

					// Retrieving the confirm message
					$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $chat['id'],
						'message' => $answer,
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);
					break;
				case 'help':
					// Checking if the chat isn't a private chat
					if ($chat['type'] !== 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because wasn&apos;t a message from a private chat (/' . $command . ' section).');
						return;
					}

					// Retrieving the help message
					$answer = $this -> getOutputMessage($language, 'help_message', '<b>FREQUENTLY ASKED QUESTION<\b>\n(FAQ list)\n\n<a href=\"(link to the manual, without brackets)\" >TELEGRAM GUIDE</a>\n\n<b>INLINE COMMANDS<\b>\n(Inline mode description).');

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $sender['id'],
						'message' => $answer,
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);
					break;
				case 'link':
					// Checking if the chat is a private chat
					if ($chat['type'] === 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from a private chat (/' . $command . ' section).');
						return;
					}

					// Retrieving the link message
					$answer = $this -> getOutputMessage($language, 'link_message', '<a href=\"${invite_link}\" >This</a> is the invite link to this chat.');

					/**
					 * Personalizing the message
					 *
					 * str_replace() replace the tag with its value
					 */
					$answer = str_replace('${invite_link}', $chat['invite'], $answer);

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $chat['id'],
						'message' => $answer,
						'reply_to_msg_id' => $message['id'],
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);
					break;
				case 'report':
					// Checking if the chat isn't a private chat
					if ($chat['type'] !== 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because wasn&apos;t a message from a private chat (/' . $command . ' section).');
						return;
					}

					// Checking if the sender is a bot's admin
					try {
						yield $this -> DB -> execute('SELECT NULL FROM `Admins` WHERE `id`=?;', [
							$sender['id']
						]);
					} catch (Amp\Sql\QueryError $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					} catch (Amp\Sql\FailureException $e) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from an unauthorized user (/' . $command . ' section).');
						return;
					}

					yield $this -> bots -> setCommands([
						'commands' => [
							[
								'_' => 'botCommand',
								'command' => 'add',
								'description' => "Assign a user as bot's admin or add a chat or a language to the database"
							],
							[
								'_' => 'botCommand',
								'command' => 'administer',
								'description' => "Commmute the 'to_administer' flag of a chat"
							],
							[
								'_' => 'botCommand',
								'command' => 'announce',
								'description' => "If it's used into the staff group, send an announce in all the (super)groups, otherwise only in the (super)group where it's used"
							],
							[
								'_' => 'botCommand',
								'command' => 'ban',
								'description' => "If it's used into the staff group, ban a user from all the (super)groups, otherwise only from the (super)group where it's used"
							],
							[
								'_' => 'botCommand',
								'command' => 'blacklist',
								'description' => "Insert a user in the bot's blacklist"
							],
							[
								'_' => 'botCommand',
								'command' => 'export_invite_link',
								'description' => 'Change the invite link of the chat'
							],
							[
								'_' => 'botCommand',
								'command' => 'help',
								'description' => 'Send the help of the bot'
							],
							[
								'_' => 'botCommand',
								'command' => 'kick',
								'description' => 'Kick a user from a (super)group'
							],
							[
								'_' => 'botCommand',
								'command' => 'link',
								'description' => 'Send the invite link of the (super)group'
							],
							[
								'_' => 'botCommand',
								'command' => 'mute',
								'description' => 'Mute a user in a (super)group'
							],
							[
								'_' => 'botCommand',
								'command' => 'remove',
								'description' => "Remove a user as bot's admin or a chat or a language to the database"
							],
							[
								'_' => 'botCommand',
								'command' => 'report',
								'description' => 'Set the bot commands'
							],
							[
								'_' => 'botCommand',
								'command' => 'silence',
								'description' => 'Mute a (super)group'
							],
							[
								'_' => 'botCommand',
								'command' => 'staff_group',
								'description' => 'Set the staff group for one or more chats'
							],
							[
								'_' => 'botCommand',
								'command' => 'start',
								'description' => 'Starts the bot'
							],
							[
								'_' => 'botCommand',
								'command' => 'unban',
								'description' => "If it's used into the staff group, unban a user from all the (super)groups, otherwise only from the (super)group where it's used"
							],
							[
								'_' => 'botCommand',
								'command' => 'unblacklist',
								'description' => "Remove a user from the bot's blacklist"
							],
							[
								'_' => 'botCommand',
								'command' => 'unmute',
								'description' => 'Unmute a user from a (super)group'
							],
							[
								'_' => 'botCommand',
								'command' => 'unsilence',
								'description' => 'Unmute a (super)group'
							],
							[
								'_' => 'botCommand',
								'command' => 'update',
								'description' => 'Update the database'
							],
							[
								'_' => 'botCommand',
								'command' => 'welcome',
								'description' => 'Set the welcome message for a chat'
							]
						]
					]);

					// Retrieving the confirm message
					$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $chat['id'],
						'message' => $answer,
						'reply_to_msg_id' => $message['id'],
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);
					break;
				case 'staff_group':
					// Checking if the chat is a private chat
					if ($chat['type'] === 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from a private chat (/' . $command . ' section).');
						return;
					}

					// Checking if the sender is a bot's admin
					try {
						yield $this -> DB -> execute('SELECT NULL FROM `Admins` WHERE `id`=?;', [
							$sender['id']
						]);
					} catch (Amp\Sql\QueryError $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					} catch (Amp\Sql\FailureException $e) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from an unauthorized user (/' . $command . ' section).');
						return;
					}

					// Retrieving the chats' list
					try {
						$result = yield $this -> DB -> query('SELECT `id`, `title` FROM `Chats`;');
					} catch (Amp\Sql\FailureException $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					}

					$chats = [];

					// Cycle on the result
					while (yield $result -> advance()) {
						$chats []= $result -> getCurrent();
					}

					/**
					 * Retrieving the length of the chats' list
					 *
					 * count() retrieve the length of the array
					 */
					$total = count($chats);

					/**
					 * Setting the Inline Keyboard
					 *
					 * array_splice() extract the sub-array from the main array
					 * array_map() convert each chat to a keyboardButtonCallback
					 */
					$chats = array_slice($chats, 0,  $this -> button_InlineKeyboard);
					$chats = array_map(function ($n) {
						return [
							'_' => 'keyboardButtonCallback',
							'text' => $n['title'],
							/**
							 * Generating the keyboardButtonCallback data
							 *
							 * base64_encode() encode the string
							 */
							'data' => base64_encode($command . '/' . $n['id'] . '/no')
						];
					}, $chats);

					$row = [
						'_' => 'keyboardButtonRow',
						'buttons' => []
					];
					$keyboard = [
						'_' => 'replyInlineMarkup',
						'rows' => []
					];

					// Cycle on the buttons' list
					foreach ($chats as $button) {
						/**
						 * Retrieving the length of the row
						 *
						 * count() retrieve the length of the array
						 */
						if (count($row['buttons']) === 2) {
							// Saving the row
							$keyboard['rows'] []= $row;

							// Creating a new row
							$row['buttons'] = [];
						}
						// Adding a button to the row
						$row['buttons'] []= $button;
					}

					// Setting the page
					if ($total > $this -> button_InlineKeyboard) {
						$keyboard['rows'] []= [
							'_' => 'keyboardButtonRow',
							'buttons' => [
								[
									'_' => 'keyboardButtonCallback',
									'text' => '',
									/**
									 * Generating the keyboardButtonCallback data
									 *
									 * base64_encode() encode the string
									 */
									'data' => base64_encode('')
								],
								[
									'_' => 'keyboardButtonCallback',
									'text' => 'Next page',
									/**
									 * Generating the keyboardButtonCallback data
									 *
									 * base64_encode() encode the string
									 */
									'data' => base64_encode($command . '/page/1')
								]
							]
						];
					}

					// Setting the confirm buttons
					$keyboard['rows'] []= [
						'_' => 'keyboardButtonRow',
						'buttons' => [
							[
								'_' => 'keyboardButtonCallback',
								'text' => 'Reject',
								/**
								 * Generating the keyboardButtonCallback data
								 *
								 * base64_encode() encode the string
								 */
								'data' => base64_encode($command . '/reject')
							],
							[
								'_' => 'keyboardButtonCallback',
								'text' => 'Confirm',
								/**
								 * Generating the keyboardButtonCallback data
								 *
								 * base64_encode() encode the string
								 */
								'data' => base64_encode($command . '/confirm')
							]
						]
					];

					// Retrieving the staff_group message
					$answer = $this -> getOutputMessage($language, 'staff_group_message', 'For what chats do you want set this staff group ?');

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $chat['id'],
						'message' => $answer,
						'reply_to_msg_id' => $message['id'],
						'parse_mode' => 'HTML',
						'clear_draft' => TRUE,
						'reply_markup' => $keyboard
					]);
					break;
				case 'start':
					// Checking if the chat isn't a private chat
					if ($chat['type'] !== 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because wasn&apos;t a message from a private chat (/' . $command . ' section).');
						return;
					}

					// Retrieving the start message
					$answer = $this -> getOutputMessage($language, 'start_message', 'Hello <a href=\"mention:${sender_id}\" >${sender_first_name}</a>, welcome !\n\n(Rest of the message to be sent upon receipt of the start command)');

					/**
					 * Personalizing the message
					 *
					 * str_replace() replace the tags with their value
					 */
					$answer = str_replace('${sender_first_name}', $sender['first_name'], $answer);
					$answer = str_replace('${sender_id}', $sender['id'], $answer);

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $sender['id'],
						'message' => $answer,
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);
					break;
				case 'update':
					// Checking if the chat isn't a private chat
					if ($chat['type'] !== 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because wasn&apos;t a message from a private chat (/' . $command . ' section).');
						return;
					}

					// Checking if the sender is a bot's admin
					try {
						yield $this -> DB -> execute('SELECT NULL FROM `Admins` WHERE `id`=?;', [
							$sender['id']
						]);
					} catch (Amp\Sql\QueryError $e) {
						$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
						return;
					} catch (Amp\Sql\FailureException $e) {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from an unauthorized user (/' . $command . ' section).');
						return;
					}

					$this -> update();

					// Retrieving the confirm message
					$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $sender['id'],
						'message' => $answer,
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);
					break;
				case 'welcome':
					// Checking if the chat is a private chat
					if ($chat['type'] === 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from a private chat (/' . $command . ' section).');
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
					if (empty($args) === FALSE) {
						/**
						 * Retrieving the admins list
						 *
						 * array_filter() filters the array by the role of each member
						 * array_map() convert each admins to its id
						 */
						$admins = array_filter($chat['participants'], function ($n) {
							return $n['role'] === 'admin' || $n['role'] === 'creator';
						});
						$admins = array_map(function ($n) {
							return $n['user']['id'];
						}, $admins);

						/**
						 * Checking if the user is an admin
						 *
						 * in_array() check if the array contains an item that match the element
						 */
						if (in_array($sender['id'], $admins) === FALSE) {
							$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the sender isn&apos;t an admin of the chat (/' . $command . ' section).');
							return;
						}

						// Opening a transaction
						$transaction = yield $this -> DB -> beginTransaction();

						// Insert the user into the blacklist
						try {
							yield $transaction -> execute('UPDATE `Chats` SET `welcome`=? WHERE `id`=?;', [
								$args,
								$chat['id']
							]);
						} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
							// Closing the transaction
							yield $transaction -> close();

							$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
							return;
						}

						// Commit the change
						yield $transaction -> commit();

						// Closing the transaction
						yield $transaction -> close();

						// Retrieving the confirm message
						$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

						yield $this -> messages -> sendMessage([
							'no_webpage' => TRUE,
							'peer' => $chat['id'],
							'message' => $answer,
							'reply_to_msg_id' => $message['id'],
							'clear_draft' => TRUE,
							'parse_mode' => 'HTML'
						]);
					}
					break;
				default:
					// Checking if the chat isn't a private chat
					if ($chat['type'] !== 'user') {
						$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because wasn&apos;t a message from a private chat.');
						return;
					}

					// Retrieving the unknown message
					$answer = $this -> getOutputMessage($language, 'unknown_message', 'This command isn&apos;t supported.');

					yield $this -> messages -> sendMessage([
						'no_webpage' => TRUE,
						'peer' => $sender['id'],
						'message' => $answer,
						'clear_draft' => TRUE,
						'parse_mode' => 'HTML'
					]);
					break;
			}

			// Checking if the chat isn't a private chat
			if ($chat['type'] !== 'user') {
				yield $this -> channels -> deleteMessages([
					'revoke' => TRUE,
					'id' => [
						$message['id']
					]
				]);
			}
		/**
		 * Checking if is the add_lang message
		 *
		 * preg_match_all() perform a RegEx match with the 'g' flag active
		 */
		} else if (preg_match_all('/^(lang_code|(add_lang|admin|confirm|help|invalid_parameter|invalid_syntax|mute|mute_advert|link|reject|staff_group|start|unknown)_message)\:[[:blank:]]?([[:alnum:][:blank:]_\<\>\/@] *)$/miu', $message['message'], $matches, PREG_SET_ORDER)) {
			// Checking if the sender is a bot's admin
			try {
				yield $this -> DB -> execute('SELECT NULL FROM `Admins` WHERE `id`=?;', [
					$sender['id']
				]);
			} catch (Amp\Sql\QueryError $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				return;
			} catch (Amp\Sql\FailureException $e) {
				$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because was a message from an unauthorized user (add language section).');
				return;
			}

			/**
			 * Retrieving the primary key
			 *
			 * array_filter() filters the array by the first group of the match
			 */
			$primary_key = array_filter($matches, function ($n) {
				return $n[1] === 'lang_code';
			});

			/**
			 * Checking if the message doesn't contains the lang_code
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
			if (empty($primary_key)) {
				$this -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because have a wrong syntax (add language section).');

				yield $this -> messages -> sendMessage([
					'no_webpage' => TRUE,
					'peer' => $chat['id'],
					'message' => 'The message wasn&apos;t managed because have a wrong syntax (<code>lang_code</code> option is missing).',
					'reply_to_msg_id' => $message['id'],
					'clear_draft' => TRUE,
					'parse_mode' => 'HTML'
				]);
				return;
			}

			/**
			 * Removing the primary key from the matches
			 *
			 * array_search() search the primary key into the array
			 * array_splice() extract the sub-array from the main array
			 */
			array_splice($matches, array_search($primary_key, $matches), 1);

			$primary_key = $primary_key[0][3];

			// Opening a transaction
			$transaction = yield $this -> DB -> beginTransaction();

			// Insert the language
			try {
				yield $transaction -> execute('INSERT INTO `Languages` (`lang_code`) VALUES (?);', [
					$primary_key
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				return;
			}

			// Commit the change
			yield $transaction -> commit();

			// Adding the messages
			try {
				$statement = yield $transaction -> prepare('UPDATE `Languages` SET ?=? WHERE `lang_code`=?;');
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				return;
			}

			// Cycle on the matches
			foreach ($matches as $match) {
				try {
					yield $statement -> execute([
						$match[1],
						$match[3],
						$primary_key
					]);
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				}
			}

			// Closing the statement
			$statement -> close();

			// Commit the change
			yield $transaction -> commit();

			// Closing the transaction
			yield $transaction -> close();

			// Retrieving the confirm message
			$answer = $this -> getOutputMessage($language, 'confirm_message', 'Operation completed.');

			yield $this -> messages -> sendMessage([
				'no_webpage' => TRUE,
				'peer' => $chat['id'],
				'message' => $answer,
				'reply_to_msg_id' => $message['id'],
				'clear_draft' => TRUE,
				'parse_mode' => 'HTML'
			]);

			// Sending the report to the channel
			$this -> logger('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> added a language (<code>' . $primary_key . '</code>) to the database.');
			$this -> report('<a href=\"mention:' . $sender['id'] . '\" >' . $sender['first_name'] . '</a> added a language (<code>' . $primary_key . '</code>) to the database.');
		} else if (preg_match_all('/^[\+-]{1,5}$/mu', $message['message'], $matches, PREG_SET_ORDER)) {
			try {
				$reputation = yield $this -> DB -> execute('SELECT `reputation` FROM `Chats_data` WHERE `user_id`=? AND `chat_id`=?;', [
					$sender['id'],
					$chat['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				return;
			}

			// Checking if the query has product a result
			if ($reputation instanceof Amp\Mysql\ResultSet === FALSE) {
				return;
			}

			/**
			 * Retrieving the +
			 *
			 * str_replace() replace the special characters with their code
			 */
			$increment = str_replace('-', '', $message['message']);

			/**
			 * Retrieving the -
			 *
			 * str_replace() replace the special characters with their code
			 */
			$decrement = str_replace('+', '', $message['message']);

			yield $reputation -> advance();
			$reputation = $reputation -> getCurrent();
			/**
			 * Retrieving the reputation of the user
			 *
			 * strlen() return the length of the argument
			 */
			$reputation = $reputation['reputation'] + strlen($increment) - strlen($decrement);

			// Checking if the reputation is too less
			if ($reputation < 0) {
				$reputation = 0;
			}

			// Opening a transaction
			$transaction = yield $this -> DB -> beginTransaction();

			// Improving the reputation of the user
			try {
				yield $transaction -> execute('UPDATE `Chats_data` SET `reputation`=? WHERE `user_id`=? AND `chat_id`=?;', [
					$reputation,
					$sender['id'],
					$chat['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$this -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				return;
			}

			// Commit the change
			yield $transaction -> commit();

			// Closing the transaction
			yield $transaction -> close();
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
			'database' => 'database_name'
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
