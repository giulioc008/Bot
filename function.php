<?php
/**
* Template for creating a Telegram bot in PHP.
* This template can be reused in accordance with the LGPL-3.0 License.
*
* This file contains all the functions necessary to the bot.
*
* @author		Giulio Coa

* @copyright	2020- Giulio Coa <giuliocoa@gmail.com>

* @license		https://choosealicense.com/licenses/lgpl-3.0/
*/

// Adding the libraries
require_once 'vendor/autoload.php';

/**
* Create the bitmask for the chat permissions.
*
* @param array $permissions The chat permissions.
*
* @return int The bitmask.
*/
function bitmask(array $permissions) : int {
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
* Retrieve the info about a chat/channel/user.
*
* @param mixed $bot The bot.
* @param int $id The id of the chat/channel/user that we want retrieve the info.
*
* @return mixed The info about the chat/channel/user.
*/
function getInfo($bot, int $id) {
	// Checking if is a correct use of the function
	if ($bot instanceof danog\MadelineProto\EventHandler === FALSE) {
		return NULL;
	}

	// Retrieving the data of the user
	$user = yield $bot -> getInfo($id);

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
		if (empty($user) || ($user['_'] !== 'chat' && $user['_'] !== 'channel')) {
			$bot -> logger('The retrieval was unsuccessful (' . $id . ').');
			return NULL;
		}
	} else {
		$user = $user['User'];
	}

	return $user;
}

/**
* Retrieve the a message.
*
* @param mixed $bot The bot.
* @param array $input_messages An array composed by the id of the messages that we want retrieve.
*
* @return array An array composed by the retrieved messages.
*/
function getMessages($bot, array $input_messages) : ?array {
	// Checking if is a correct use of the function
	if ($bot instanceof danog\MadelineProto\EventHandler === FALSE) {
		return NULL;
	}

	$messages = yield $bot -> messages -> getMessages([
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

		$bot -> logger('Message retrieval was unsuccessful (' . $messages . ').');
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
* Retrieve the language of the user and check if the bot supports it.
*
* @param mixed $bot The bot.
* @param string $language The language of the user.
*
* @return string The language of the user.
*/
function getLanguage($bot, string $language) : ?string {
	// Checking if is a correct use of the function
	if ($bot instanceof danog\MadelineProto\EventHandler === FALSE) {
		return NULL;
	}

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
		yield $bot -> DB -> execute('SELECT NULL FROM `Languages` WHERE `lang_code`=?;', [
			$language
		]);
	} catch (Amp\Sql\QueryError $e) {
		$bot -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);
		$language = 'en';
	} catch (Amp\Sql\FailureException $e) {
		$language = 'en';
	}

	return $language;
}

/**
* Retrieve an output message from the database.
*
* @param mixed $bot The bot.
* @param string $language The language of the user.
* @param string $message_name The name of the message.
* @param string $default_message The default message.
*
* @return string The output message.
*/
function getOutputMessage($bot, string $language, string $message_name, string $default_message) : string {
	// Retrieving the message
	try {
		$answer = yield $bot -> DB -> execute('SELECT ? FROM `Languages` WHERE `lang_code`=?;', [
			$message_name,
			$language
		]);
	} catch (Amp\Sql\QueryError $e) {
		$bot -> logger('Failed to make the query, because ' . $e -> getMessage(), \danog\MadelineProto\Logger::ERROR);
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
* Check if a user is already into the database (Chats_data section) and modify its data.
* In case the user isn't into the database, adds it.
*
* @param mixed $bot The bot.
* @param int $chat_id The id of the chat/channel that the user joined.
* @param int $user_id The id of the user.
*
* @return void
*/
function insertChatsData($bot, int $chat_id, int $user_id) {
	// Checking if is a correct use of the function
	if ($bot instanceof danog\MadelineProto\EventHandler === FALSE) {
		return;
	}

	// Checking if the user was be a member
	try {
		$result = yield $bot -> DB -> execute('SELECT `entrances` FROM `Chats_data` WHERE `user_id`=? AND `chat_id`=?;', [
			$user_id,
			$chat_id
		]);
	} catch (Amp\Sql\QueryError $e) {
		$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		return;
	} catch (Amp\Sql\FailureException $e) {
		// Opening a transaction
		$transaction = yield $bot -> DB -> beginTransaction();

		try {
			yield $transaction -> execute('INSERT INTO `Chats_data` (`chat_id`, `user_id`) VALUES (?, ?);', [
				$chat_id,
				$user_id
			]);
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
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
	$transaction = yield $bot -> DB -> beginTransaction();

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
		$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		return;
	}

	// Commit the change
	yield $transaction -> commit();

	// Closing the transaction
	yield $transaction -> close();
}

/**
* Updates the database.
*
* @param mixed $bot The bot.
* @param bool $print [Optional] The flag that tells if the function must print an output or not.
* @param string $language [Optional] The language of the user that send the /update command.
* @param int $sender [Optional] The id of the user that send the /update command.
*
* @return void
*/
function update($bot, bool $print = FALSE, string $language = '', int $sender = 0) {
	// Checking if is a correct use of the function
	if ($bot instanceof danog\MadelineProto\EventHandler === FALSE) {
		return;
	}

	$banned = [
		'multiple' = TRUE
	];

	// Retrieving the chats' list
	try {
		$result = yield $bot -> DB -> query('SELECT `id` FROM `Chats`;');
	} catch (Amp\Sql\FailureException $e) {
		$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		return;
	}

	$chats = [];

	// Cycle on the result
	while (yield $result -> advance()) {
		$sub_chat = $result -> getCurrent();

		// Retrieving the data of the chat
		$sub_chat = getInfo($bot, $sub_chat['id']);

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
	$transaction = yield $bot -> DB -> beginTransaction();

	// Updating the chats' data
	try {
		$statement = yield $transaction -> prepare('UPDATE `Chats` SET `title`=?, `username`=?, `invite_link`=?, `permissions`=? WHERE `id`=?;');
	} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
		$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

		// Closing the transaction
		yield $transaction -> close();
		return;
	}

	// Cycle on the list of the chats
	foreach ($chats as $sub_chat) {
		if ($sub_chat['_'] === 'chat' && $sub_chat['migrated_to']['_'] !== 'inputChannelEmpty') {
			$old_id = $sub_chat['id'];

			$sub_chat = yield $bot -> getPwrChat($sub_chat['migrated_to']['channel_id']);

			$permissions = getInfo($bot, $sub_chat['id']);

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
				$bot -> logger('The update ' . $sub_chat['id'] . ' of wasn&apos;t complete because the retrieve process of the default permissions of the chat failed.');
			}

			// Closing the statement
			$statement -> close();

			$bitmask = bitmask($permissions['default_banned_rights']);

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
				$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

				try {
					$statement = yield $transaction -> prepare('UPDATE `Chats` SET `title`=?, `username`=?, `invite_link`=?, `permissions`=? WHERE `id`=?;');
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

					// Closing the transaction
					yield $transaction -> close();
					return;
				}
				continue;
			}

			// Commit the change
			$transaction -> commit();

			$sub_chat = [
				'id' => $sub_chat['id'],
				'type' => $sub_chat['type'],
				'title' => $sub_chat['title'],
				'username' => $sub_chat['username'],
				'invite_link' => $sub_chat['invite'],
				'permissions' => $bitmask
			];

			try {
				$statement = yield $transaction -> prepare('UPDATE `Chats` SET `title`=?, `username`=?, `invite_link`=?, `permissions`=? WHERE `id`=?;');
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

				// Closing the transaction
				yield $transaction -> close();
				return;
			}
			continue;
		}

		$link = yield $bot -> getPwrChat($sub_chat['id']);
		$link = $link['invite'];

		$bitmask = bitmask($sub_chat['default_banned_rights']);

		try {
			yield $statement -> execute([
				$sub_chat['title'],
				$sub_chat['username'],
				$link,
				$bitmask,
				$sub_chat['id']
			]);
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		}

		$sub_chat['invite_link'] = $link;
		$sub_chat['permissions'] = $bitmask;
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
		// Retrieving the data of the chat
		$sub_chat = yield $bot -> getPwrChat($sub_chat);

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
			$result = yield $bot -> getHttpClient() -> request(new Amp\Http\Client\Request('https://api.cas.chat/check?user_id=' . $member));

			// Retrieving the result
			$result = yield $result -> getBody() -> buffer();

			$result = json_decode($result, TRUE);

			// Retrieving the data of the new member
			$member = getInfo($bot, $member);

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
			$transaction = yield $bot -> DB -> beginTransaction();

			// Removing the data of the user
			try {
				yield $transaction -> execute('DELETE FROM `Chats_data` WHERE `user_id`=?;', [
					$member['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
			try {
				yield $transaction -> execute('DELETE FROM `Penalty` WHERE `user_id`=?;', [
					$member['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}

			// Commit the change
			yield $transaction -> commit();

			// Closing the transaction
			yield $transaction -> close();
		}
	}

	yield $bot -> channels -> editBanned($banned);

	/**
	* Retrieving the admins' list
	*
	* array_map() convert admin to its id
	*/
	try {
		$result = yield $bot -> DB -> query('SELECT `id` FROM `Admins`;');
	} catch (Amp\Sql\FailureException $e) {
		$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
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
	$transaction = yield $bot -> DB -> beginTransaction();

	// Updating the admins' data
	try {
		$statement = yield $transaction -> prepare('DELETE FROM `Admins` WHERE `id`=?;');
	} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
		$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);

		// Closing the transaction
		yield $transaction -> close();
		return;
	}

	// Cycle on the list of the admins
	foreach ($admins as $id) {
		$admin = getInfo($bot, $id);

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
			$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			continue;
		}

		// Removing the data of the user
		try {
			yield $transaction -> execute('DELETE FROM `Chats_data` WHERE `user_id`=?;', [
				$admin['id']
			]);
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		}
		try {
			yield $transaction -> execute('DELETE FROM `Penalty` WHERE `user_id`=?;', [
				$admin['id']
			]);
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		}
		try {
			yield $transaction -> execute('DELETE FROM `Penalty` WHERE `execute_by`=?;', [
				$admin['id']
			]);
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		}
	}

	// Closing the statement
	$statement -> close();

	// Commit the change
	yield $transaction -> commit();

	// Closing the transaction
	yield $transaction -> close();

	// Checking if the function must print an output
	if ($print) {
		$answer = 'Operation completed.';

		/**
		* Checking if the language of the user that send the /update command is setted
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
		if (empty($language) === FALSE) {
			// Retrieving the confirm message
			$answer = getOutputMessage($bot, $language, 'confirm_message', 'Operation completed.');
		}


		/**
		* Checking if the id of the user that send the /update command is setted
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
			yield $bot -> messages -> sendMessage([
				'no_webpage' => TRUE,
				'peer' => $sender,
				'message' => $answer,
				'clear_draft' => TRUE,
				'parse_mode' => 'HTML'
			]);
		}
	}
}
