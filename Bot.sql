DROP DATABASE IF EXISTS `Bot`;
CREATE DATABASE `Bot` DEFAULT CHARACTER SET utf8;
USE `Bot`;

CREATE TABLE `Admins` (
  `id` BIGINT,
  `first_name` TEXT DEFAULT NULL,
  `last_name` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

CREATE TABLE `Blacklist` (
  `id` BIGINT,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

CREATE TABLE `Chats` (
  `id` BIGINT,
  `type` TEXT NOT NULL,
  `title` TEXT DEFAULT NULL,
  `username` VARCHAR(32) UNIQUE DEFAULT NULL,
  `invite_link` TEXT DEFAULT NULL,
  `welcome` LONGTEXT DEFAULT NULL,		/* i.e. "Hello ${mentions} welcome to this chat !\n\n(Rest of the message to be sent when a user join the chat)" */
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

CREATE TABLE `Data` (
  `id` BIGINT,
  `staff_group` BIGINT DEFAULT 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

CREATE TABLE `Languages` (
  `lang_code` VARCHARACTER(20),			/* i.e. "en" */
  `admin_message` TEXT DEFAULT NULL,	/* i.e. "<a href=\"mention:${admin_id}\" >${admin_first_name}</a>,\n<a href=\"mention:${sender_id}\" >${sender_first_name}</a> needs your help${motive} into <a href=\"${chat_invite}\" >${chat_title}</a>." */
  `help_message` TEXT DEFAULT NULL,		/* i.e. "<b>FREQUENTLY ASKED QUESTION<\b>\n(FAQ list)\n\n<a href=\"(link to the manual, without brackets)\" >TELEGRAM GUIDE</a>\n\n<b>INLINE COMMANDS<\b>\n(Inline mode description)" */
  `mute_message` TEXT DEFAULT NULL,		/* i.e. "The syntax of the command is: <code>/mute [time]</code>.\nThe <code>time</code> option must be more then 30 seconds and less of 366 days." */
  `link_message` TEXT DEFAULT NULL,		/* i.e. "<a href=\"${invite_link}\" >This</a> is the invite link to this chat." */
  `start_message` TEXT DEFAULT NULL,	/* i.e. "Hello <a href=\"mention:${sender_id}\" >${sender_first_name}</a>, welcome !\n\n(Rest of the message to be sent upon receipt of the start command)" */
  `unknown_message` TEXT DEFAULT NULL,	/* i.e. "This command isn\'t supported." */
  PRIMARY KEY (`lang_code`)
) DEFAULT CHARACTER SET utf8;
