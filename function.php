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

	return $bitmask;
}

/**
* Updates the database.
*
* @param mixed $bot The bot.
* @param bool $log [Optional] The flag that tells if the function must log or not.
* @param bool $print [Optional] The flag that tells if the function must print an output or not.
* @param string $language [Optional] The language of the user that send the /update command.
* @param int $sender [Optional] The id of the user that send the /update command.
*
* @return void
*/
function update($bot, bool $log = FALSE, bool $print = FALSE, string $language = '', int $sender = 0) {
	// Checking if is a correct use of the function
	if ($bot instanceof danog\MadelineProto\EventHandler === FALSE) {
		return;
	}

	$banned = [
		'multiple' = TRUE
	];

	// Retrieving the chats' list
	try {
		yield $bot -> DB -> query('SELECT `id` FROM `Chats`;');
	} catch (Amp\Sql\FailureException $e) {
		// Checking if the function must log
		if ($log) {
			$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		}
		return;
	}

	$chats = [];

	// Cycle on the result
	while (yield $result -> advance()) {
		$sub_chat = $result -> getCurrent();

		// Retrieving the data of the chat
		$sub_chat = yield $bot -> getInfo($sub_chat['id']);
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
		if (empty($sub_chat) || ($sub_chat['_'] !== 'chat' && $sub_chat['_'] !== 'channel')) {
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
		// Checking if the function must log
		if ($log) {
			$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		}

		// Closing the transaction
		yield $transaction -> close();
		return;
	}

	// Cycle on the list of the chats
	foreach ($chats as $sub_chat) {
		if ($sub_chat['_'] === 'chat' && $sub_chat['migrated_to']['_'] !== 'inputChannelEmpty') {
			$old_id = $sub_chat['id'];

			$sub_chat = yield $bot -> getPwrChat($sub_chat['migrated_to']['channel_id']);

			$permissions = yield $bot -> getInfo($sub_chat['id']);
			$permissions = $permissions['Chat'] ?? NULL;

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
			if (empty($permissions) || ($permissions['_'] !== 'chat' && $permissions['_'] !== 'channel')) {
				// Checking if the function must log
				if ($log) {
					$bot -> logger('The Message ' . $update['id'] . ' wasn&apos;t managed because the retrieve process of the default permissions of the chat failed (/' . $command . ' section).');
				}
				continue;
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
				// Checking if the function must log
				if ($log) {
					$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				}

				try {
					$statement = yield $transaction -> prepare('UPDATE `Chats` SET `title`=?, `username`=?, `invite_link`=?, `permissions`=? WHERE `id`=?;');
				} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
					// Checking if the function must log
					if ($log) {
						$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
					}

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
				// Checking if the function must log
				if ($log) {
					$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				}

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
			// Checking if the function must log
			if ($log) {
				$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
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
			$member = yield $bot -> getInfo($member);
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
			if ($result['ok'] === FALSE && empty($member) && $member['_'] === 'user' && $member['scam'] === FALSE && $member['deleted'] === FALSE) {
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
				// Checking if the function must log
				if ($log) {
					$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				}
			}
			try {
				yield $transaction -> execute('DELETE FROM `Penalty` WHERE `user_id`=?;', [
					$member['id']
				]);
			} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
				// Checking if the function must log
				if ($log) {
					$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				}
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
		// Checking if the function must log
		if ($log) {
			$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		}
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
		// Checking if the function must log
		if ($log) {
			$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
		}

		// Closing the transaction
		yield $transaction -> close();
		return;
	}

	// Cycle on the list of the admins
	foreach ($admins as $id) {
		$admin = yield $bot -> getInfo($id);
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
		if (empty($admin) === FALSE && $admin['_'] === 'user' && $admin['deleted'] === FALSE) {
			continue;
		}

		try {
			yield $statement -> execute([
				$admin['id']
			]);
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			// Checking if the function must log
			if ($log) {
				$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
			continue;
		}

		// Removing the data of the user
		try {
			yield $transaction -> execute('DELETE FROM `Chats_data` WHERE `user_id`=?;', [
				$admin['id']
			]);
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			// Checking if the function must log
			if ($log) {
				$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
		}
		try {
			yield $transaction -> execute('DELETE FROM `Penalty` WHERE `user_id`=?;', [
				$admin['id']
			]);
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			// Checking if the function must log
			if ($log) {
				$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
		}
		try {
			yield $transaction -> execute('DELETE FROM `Penalty` WHERE `execute_by`=?;', [
				$admin['id']
			]);
		} catch (Amp\Sql\QueryError | Amp\Sql\FailureException $e) {
			// Checking if the function must log
			if ($log) {
				$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
			}
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
			try {
				$answer = yield $bot -> DB -> execute('SELECT `confirm_message` FROM `Languages` WHERE `lang_code`=?;', [
					$language
				]);
			} catch (Amp\Sql\QueryError $e) {
				// Checking if the function must log
				if ($log) {
					$bot -> logger('Failed to make the query, because ' . $e -> getMessage() . '.', \danog\MadelineProto\Logger::ERROR);
				}
				$answer = 'Operation completed.';
			} catch (Amp\Sql\FailureException $e) {
				$answer = 'Operation completed.';
			}

			// Checking if the query has product a result
			if ($answer instanceof Amp\Mysql\ResultSet) {
				yield $answer -> advance();
				$answer = $answer -> getCurrent();
				$answer = $answer['confirm_message'];
			}

			/**
			* Checking if the confirm message isn't setted
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
				$answer = 'Operation completed.';
			}
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
