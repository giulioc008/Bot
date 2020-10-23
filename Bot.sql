CREATE TABLE `Languages` (
	`lang_code` VARCHAR(20),							/* i.e. "en" */
	`add_lang_message` LONGTEXT DEFAULT NULL,			/* i.e. "Send me a message with this format:\n\n<code>lang_code: &lt;insert here the lang_code of the language&gt;\nadd_lang_message: &lt;insert here the message for the /add command when a user want add a language&gt;\nadmin_message: &lt;insert here the message for the @admin tag&gt;\nconfirm_message: &lt;insert here a generic confirm message&gt;\nhelp_message: &lt;insert here the message for the /help command&gt;\ninvalid_parameter_message: &lt;insert here the message that will be sent when a user insert an invalid parameter into a command&gt;\ninvalid_syntax_message: &lt;insert here the message that will be sent when a user send a command with an invalid syntax&gt;\nmute_message: &lt;insert here the message for the /mute command&gt;\nmute_advert_message: &lt;insert here the message for when the /mute command is used with time set to forever&gt;\nlink_message: &lt;insert here the message for the /link command&gt;\nreject_message: &lt;insert here a generic reject message&gt;\nstaff_group_message: &lt;insert here the message for the /staff_group command&gt;\nstart_message: &lt;insert here the message for the /start command&gt;\nunknown_message: &lt;insert here the message for the unknown commands&gt;\nupdate_message: &lt;insert here the message for the /update command&gt;</code>\n\n<b>N.B.</b>: If you want insert a new line in the messages, you must codify it as <code>\n</code>." */
	`admin_message` LONGTEXT DEFAULT NULL,				/* i.e. "<a href=\"mention:${admin_id}\" >${admin_first_name}</a>,\n<a href=\"mention:${sender_id}\" >${sender_first_name}</a> needs your help${motive} into <a href=\"${chat_invite}\" >${chat_title}</a>." */
	`confirm_message` LONGTEXT DEFAULT NULL,			/* i.e. "Operation completed." */
	`help_message` LONGTEXT DEFAULT NULL,				/* i.e. "<b>FREQUENTLY ASKED QUESTION<\b>\n(FAQ list)\n\n<a href=\"(link to the manual, without brackets)\" >TELEGRAM GUIDE</a>\n\n<b>INLINE COMMANDS<\b>\n(Inline mode description)" */
	`invalid_parameter_message` LONGTEXT DEFAULT NULL,	/* i.e. "The ${parameter} is invalid." */
	`invalid_syntax_message` LONGTEXT DEFAULT NULL,		/* i.e. "The syntax of the command is: <code>${syntax}</code>." */
	`mute_message` LONGTEXT DEFAULT NULL,				/* i.e. "The syntax of the command is: <code>/mute [time]</code>.\nThe <code>time</code> option must be more then 30 seconds and less of 366 days.\nIf you want, you can use the short syntax for the unit time." */
	`mute_advert_message` LONGTEXT DEFAULT NULL,		/* i.e. "You have muted <a href=\"mention:${user_id}\" >${user_first_name}</a> forever." */
	`link_message` LONGTEXT DEFAULT NULL,				/* i.e. "<a href=\"${invite_link}\" >This</a> is the invite link to this chat." */
	`reject_message` LONGTEXT DEFAULT NULL,				/* i.e. "Operation deleted." */
	`staff_group_message` LONGTEXT DEFAULT NULL,		/* i.e. "For what chats do you want set this staff group ?" */
	`start_message` LONGTEXT DEFAULT NULL,				/* i.e. "Hello <a href=\"mention:${sender_id}\" >${sender_first_name}</a>, welcome !\n\n(Rest of the message to be sent upon receipt of the start command)" */
	`unknown_message` LONGTEXT DEFAULT NULL,			/* i.e. "This command isn&apos;t supported." */
	PRIMARY KEY (`lang_code`)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4_bin COLLATE utf8mb4_unicode_ci;

INSERT INTO `Languages` VALUES
('en', 'Send me a message with this format:\n\n<code>lang_code: &lt;insert here the lang_code of the language&gt;\nadd_lang_message: &lt;insert here the message for the /add command when a user want add a language&gt;\nadmin_message: &lt;insert here the message for the @admin tag&gt;\nconfirm_message: &lt;insert here a generic confirm message&gt;\nhelp_message: &lt;insert here the message for the /help command&gt;\ninvalid_parameter_message: &lt;insert here the message that will be sent when a user insert an invalid parameter into a command&gt;\ninvalid_syntax_message: &lt;insert here the message that will be sent when a user send a command with an invalid syntax&gt;\nmute_message: &lt;insert here the message for the /mute command&gt;\nmute_advert_message: &lt;insert here the message for when the /mute command is used with time set to forever&gt;\nlink_message: &lt;insert here the message for the /link command&gt;\nreject_message: &lt;insert here a generic reject message&gt;\nstaff_group_message: &lt;insert here the message for the /staff_group command&gt;\nstart_message: &lt;insert here the message for the /start command&gt;\nunknown_message: &lt;insert here the message for the unknown commands&gt;\nupdate_message: &lt;insert here the message for the /update command&gt;</code>\n\n<b>N.B.</b>: If you want insert a new line in the messages, you must codify it as <code>\n</code>.', '<a href=\"mention:${admin_id}\" >${admin_first_name}</a>,\n<a href=\"mention:${sender_id}\" >${sender_first_name}</a> needs your help${motive} into <a href=\"${chat_invite}\" >${chat_title}</a>.', 'Operation completed.', NULL, 'The ${parameter} is invalid.', 'The syntax of the command is: <code>${syntax}</code>.', 'The syntax of the command is: <code>/mute [time]</code>.\nThe <code>time</code> option must be more then 30 seconds and less of 366 days.\nIf you want, you can use the short syntax for the unit time.', 'You have muted <a href=\"mention:${user_id}\" >${user_first_name}</a> forever.', '<a href=\"${invite_link}\" >This</a> is the invite link to this chat.', 'Operation deleted.', 'For what chats do you want set this staff group ?', NULL, 'This command isn&apos;t supported.'),
('it', 'Inviami un messaggio con questo formato:\n\n<code>lang_code: &lt;inserisci qu&agrave; il codice della lingua&gt;\nadd_lang_message: &lt;inserisci qu&agrave; il messaggio per il comando /add relativo a quanto un utente vuole aggiungere una lingua&gt;\nadmin_message: &lt;inserisci qu&agrave; il messaggio per il tag @admin&gt;\nconfirm_message: &lt;inserisci qu&agrave; un generico messaggio di conferma&gt;\nhelp_message: &lt;inserisci qu&agrave; il messaggio per il comando /help&gt;\ninvalid_parameter_message: &lt;inserisci qu&agrave; il messaggio che verr&agrave; inviato quando un utente inserir&agrave;, in un comando, un parametro non valido&gt;\ninvalid_syntax_message: &lt;inserisci qu&agrave; il messaggio che verr&agrave; inviato quando un utente invier&agrave; un comando con una sintassi non valida&gt;\nmute_message: &lt;inserisci qu&agrave; il messaggio per il comando /mute&gt;\nmute_advert_message: &lt;inserisci qu&agrave; il messaggio per quando il comando /mute &egrave; usato con l&apos;opzione time impostata su &apos;forever&apos;&gt;\nlink_message: &lt;inserisci qu&agrave; il messaggio per il comando /link&gt;\nreject_message: &lt;inserisci qu&agrave; un generico messaggio di annullamento&gt;\nstaff_group_message: &lt;inserisci qu&agrave; il messaggio per il comando /staff_group&gt;\nstart_message: &lt;inserisci qu&agrave; il messaggio per il comando /start&gt;\nunknown_message: &lt;inserisci qu&agrave; il messaggio per i comandi sconosciuti&gt;\nupdate_message: &lt;inserisci qu&agrave; il messaggio per il comando /update&gt;</code>\n\n<b>N.B.</b>: Se vuoi inserire un a capo nei messaggi, devi codificarlo come <code>\n</code>.', '<a href=\"mention:${admin_id}\" >${admin_first_name}</a>,\n<a href=\"mention:${sender_id}\" >${sender_first_name}</a> ha bisogno del tuo aiuto${motive} in <a href=\"${chat_invite}\" >${chat_title}</a>.', 'Operazione completata.', NULL, 'Il ${parameter} non &egrave; valido.', 'La sintassi del comando &egrave;: <code>${syntax}</code>.', 'La sintassi del comando &egrave;: <code>/mute [time]</code>.\nL&apos;opzione <code>time</code> dev&apos;essere maggiore di 30 secondi e minore di 366 giorni.\nSe vuoi, puoi usare la sintassi corta per le unit&agrave; di tempo.', 'Hai mutato <a href=\"mention:${user_id}\" >${user_first_name}</a> a tempo indeterminato.', '<a href=\"${invite_link}\" >Questo</a> &egrave; il link d&apos;invito per questa chat.', 'Operazione cancellata.', 'Per quali chat vuoi settare questo gruppo staff ?', NULL, 'Questo comando non &egrave; supportato.');

CREATE TABLE `Users` (
	`id` BIGINT,
	`first_name` TEXT NOT NULL,
	`last_name` TEXT DEFAULT NULL,
	`lang_code` TEXT DEFAULT NULL,
	`first_use` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`last_use` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
	CHECK(`last_use` >= `first_use`)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4_bin COLLATE utf8mb4_unicode_ci;

CREATE TABLE `Admins` (
	`id` BIGINT,
	`added_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`added_by` BIGINT NOT NULL,
	`owner` TINYINT(1) NOT NULL DEFAULT 0,
	`permissions` INT NOT NULL DEFAULT 2,						/* This bitmask rappresent the permissions of the admin; it are, from the MSB to the LSB, 'bot', 'chat' and 'penalty'. */
	PRIMARY KEY (`id`),
	FOREIGN KEY (`id`)
		REFERENCES `Users`(`id`)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	FOREIGN KEY (`added_by`)
		REFERENCES `Admins`(`id`)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4_bin COLLATE utf8mb4_unicode_ci;

CREATE TABLE `Blacklist` (
	`id` BIGINT,
	`banned_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`banned_by` BIGINT NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`id`)
		REFERENCES `Users`(`id`)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	FOREIGN KEY (`banned_by`)
		REFERENCES `Admins`(`id`)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4_bin COLLATE utf8mb4_unicode_ci;

CREATE TABLE `Chats` (
	`id` BIGINT,
	`type` TEXT NOT NULL,
	`title` TEXT DEFAULT NULL,
	`username` VARCHAR(32) UNIQUE DEFAULT NULL,
	`invite_link` TEXT DEFAULT NULL,
	`welcome` LONGTEXT DEFAULT NULL,					/* i.e. "Hello ${mentions} welcome to this chat !\n\n(Rest of the message to be sent when a user join the chat)" */
	`staff_group` BIGINT DEFAULT NULL,
	`to_administer` TINYINT(1) NOT NULL DEFAULT 0,
	`permissions` INT NOT NULL DEFAULT 2042,			/* This bitmask rappresent the permissions of the chat; it are, from the MSB to the LSB, 'send_messages', 'send_media', 'send_stickers', 'send_gifs', 'send_games', 'send_inline', 'embed_links', 'send_polls', 'change_info', 'invite_users' and 'pin_messages'. */
	PRIMARY KEY (`id`),
	FOREIGN KEY (`staff_group`)
		REFERENCES `Chats`(`id`)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4_bin COLLATE utf8mb4_unicode_ci;

CREATE TABLE `Chats_data` (
	`chat_id` BIGINT,
	`user_id` BIGINT,
	`experience` INT NOT NULL DEFAULT 0,
	`reputation` INT NOT NULL DEFAULT 0,
	`ttl` DATETIME DEFAULT NULL,
	`entrances` INT NOT NULL DEFAULT 1,
	`last_join` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`chat_id`, `user_id`),
	FOREIGN KEY (`chat_id`)
		REFERENCES `Chats`(`id`)
		ON DELETE CASCADE
		ON UPDATE NO ACTION,
	FOREIGN KEY (`user_id`)
		REFERENCES `Users`(`id`)
		ON DELETE CASCADE
		ON UPDATE NO ACTION,
	CHECK(`experience` >= 0),
	CHECK(`reputation` >= 0),
	CHECK(`ttl` >= 0)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4_bin COLLATE utf8mb4_unicode_ci;

CREATE TABLE `Messages_to_delete` (
	`id` BIGINT,
	`ttl` DATETIME NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4_bin COLLATE utf8mb4_unicode_ci;

CREATE TABLE `Penalty` (
	`chat_id` BIGINT,
	`user_id` BIGINT,
	`type` VARCHAR(20),
	`id` INT,
	`execute_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `execute_by` BIGINT NOT NULL,
	PRIMARY KEY (`chat_id`, `user_id`, `type`, `id`),
	FOREIGN KEY (`chat_id`)
		REFERENCES `Chats`(`id`)
		ON DELETE CASCADE
		ON UPDATE NO ACTION,
	FOREIGN KEY (`user_id`)
		REFERENCES `Users`(`id`)
		ON DELETE CASCADE
		ON UPDATE NO ACTION,
	FOREIGN KEY (`execute_by`)
		REFERENCES `Users`(`id`)
		ON DELETE CASCADE
		ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4_bin COLLATE utf8mb4_unicode_ci;
