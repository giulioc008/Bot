from apscheduler.schedulers.asyncio import AsyncIOScheduler
from apscheduler.triggers.interval import IntervalTrigger
import asyncio
import logging as logger
import pymysql
from pyrogram import CallbackQuery, ChatPermission, Client, Filters, InlineKeyboardButton, InlineKeyboardMarkup, InlineQuery, InlineQueryResultArticle, KeyboardButton, Message, ReplyKeyboardMarkup
from pyrogram.errors import FloodWait
import re
import res
from res import Configurations

configurations_map = {
	"commands": "commands",
	"database": "database",
	"logger": "logger"
}

loop = asyncio.get_event_loop()

config = Configurations("config/config.json", configurations_map)
loop.run_until_complete(config.parse())
config.set("app_hash", os.environ.pop("app_hash", None))
config.set("app_id", int(os.environ.pop("app_id", None)))
config.set("bot_token", os.environ.pop("bot_token", None))
config.set("bot_username", os.environ.pop("bot_username", None))

connection = pymysql.connect(
	host=config.get("database")["host"],
	user=os.environ.pop("database_username", config.get("database")["username"]),
	password=os.environ.pop("database_password", config.get("database")["password"]),
	database=config.get("database")["name"],
	port=config.get("database")["port"],
	charset="utf8",
	cursorclass=pymysql.cursors.DictCursor,
	autocommit=False)

logger.basicConfig(
	filename=config.get("logger")["path"],
	datefmt="%d/%m/%Y %H:%M:%S",
	format=config.get("logger")["format"],
	level=config.get("logger").pop("level", logger.INFO))

minute = 60
scheduler = AsyncIOScheduler()

with connection.cursor() as cursor:
	logger.info("Setting the admins list ...")
	cursor.execute("SELECT `id` FROM `Admins` WHERE `username`=%(user)s;", {
		"user": "username"
	})
	config.set("creator", cursor.fetchone()["id"])

	cursor.execute("SELECT `id` FROM `Admins`;")
	admins_list = list(map(lambda n: n["id"], cursor.fetchall()))

	cursor.execute("SELECT * FROM `Blackist`;")
	blacklist = list(map(lambda n: n["id"], cursor.fetchall()))

	logger.info("Admins setted\nSetting the chats list ...")
	cursor.execute("SELECT `id` FROM `Chats`;")
	chats_list = list(map(lambda n: n["id"], cursor.fetchall()))

logger.info("Chats initializated\nInitializing the Client ...")
app = Client(session_name=config.get("bot_username"), api_id=config.get("app_id"), api_hash=config.get("app_hash"), bot_token=config.get("bot_token"), lang_code="it")


@app.on_message(Filters.command("add", prefixes="/") & (Filters.user(config.get("creator")) | Filters.channel))
async def add_to_the_database(client: Client, message: Message):
	# /add
	global admins_list, chats_list, config, connection

	message.command.pop(0)

	# Checking if the data are of a chat or of a user
	if message.reply_to_message is not None:
		# Checking if the user is in the admins list
		if message.reply_to_message.from_user.id in admins_list:
			await res.split_reply_text(config, message, "The user @{} is already an admin.".format(message.reply_to_message.from_user.username), quote=False)
			logger.info("{} have sent an incorrect /add request.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))
			return

		# Retrieving the data of the new admin
		chat = message.reply_to_message.from_user

		# Adding the new admin to the list
		admins_list.append(chat["id"])
	else:
		# Checking if the chat is in the list
		if message.chat.id in chats_list:
			await res.split_reply_text(config, message, "The chat {} is already present in the list of allowed chat.".format(message.chat.title), quote=False)
			logger.info("{} have sent an incorrect /add request.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))
			return

		# Retrieving the data of the chat
		chat = message.chat
		chat = chat.__dict__

		# Deleting the message
		await message.delete(revoke=True)

		# Adding the chat to the list
		chats_list.append(chat["id"])

	# Removing inutil informations
	chat.pop("_client", None)
	chat.pop("_", None)
	chat.pop("photo", None)
	chat.pop("description", None)
	chat.pop("pinned_message", None)
	chat.pop("sticker_set_name", None)
	chat.pop("can_set_sticker_set", None)
	chat.pop("members_count", None)
	chat.pop("restrictions", None)
	chat.pop("permissions", None)
	chat.pop("distance", None)
	chat.pop("status", None)
	chat.pop("last_online_date", None)
	chat.pop("next_offline_date", None)
	chat.pop("dc_id", None)
	chat.pop("is_self", None)
	chat.pop("is_contact", None)
	chat.pop("is_mutual_contact", None)
	chat.pop("is_deleted", None)
	chat.pop("is_bot", None)
	chat.pop("is_verified", None)
	chat.pop("is_restricted", None)
	chat.pop("is_scam", None)
	chat.pop("is_support", None)
	chat.pop("language_code", None)

	with connection.cursor() as cursor:
		if chat.get("type", None) is None:
			# Adding the user to the database
			cursor.execute("INSERT INTO `Admins` (`id`, `first_name`, `last_name`, `username`, `phone_number`) VALUES (%(id)s, %(first_name)s, %(last_name)s, %(username)s, %(phone_number)s);", chat)

			await message.chat.promote_member(chat["id"], can_change_info=True, can_post_messages=True, can_edit_messages=False, can_delete_messages=True, can_restrict_members=True, can_invite_users=True, can_pin_messages=True, can_promote_members=False)
			for i in chats_list:
				# Checking if the user is in the chat
				try:
					await client.get_chat_member(i, chat["id"])
				except ChannelInvalid:
					# Updating the player's privilege
					await client.promote_chat_member(i, chat["id"], can_change_info=True, can_post_messages=True, can_edit_messages=False, can_delete_messages=True, can_restrict_members=True, can_invite_users=True, can_pin_messages=True, can_promote_members=False)

			text = "Admin added to the database."
		else:
			# Adding the chats to the database
			cursor.execute("INSERT INTO `Chats` (`id`, `type`, `title`, `username`, `first_name`, `last_name`, `invite_link`, `welcome`, `domain`) VALUES (%(id)s, %(type)s, %(title)s, %(username)s, %(first_name)s, %(last_name)s, %(invite_link)s,  %(welcome)s, %(domain)s);", chat)

			text = "Chat added to the database."
	connection.commit()

	await res.split_reply_text(config, message, text, quote=False)
	logger.info("I\'ve answered to /add because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.chat(chats_list) & Filters.regex("^(\@admin)\s+(\S+.*)$", re.IGNORECASE | re.UNICODE | re.MULTILINE) & ~Filters.user(blacklist))
async def admin(client: Client, message: Message):
	global config, connection

	# Retrieving the admins
	with connection.cursor() as cursor:
		cursor.execute("SELECT `id`, `first_name`, `username` FROM `Admins`")
		admins = cursor.fetchall()

	if message.from_user.username is not None:
		text = "\n@{} needs your help".format(message.from_user.username)
	else:
		text = "\n<a href=\"tg://user?id={}\">{}</a> needs your help".format(message.from_user.id, message.from_user.first_name)

	# Retrieving the eventual message for the admins
	match = message.matches.pop(0)
	if match.group(2) != "":
		text += " for {}".format(match.group(2))
	text += "."

	# Tagging the admins
	await res.split_reply_text(config, message.reply_to_message, " ".join(list(map(lambda n: "@{}".format(i["username"]) if i["username"] is not None else "<a href=\"tg://user?id={}\">{}</a>".format(i["id"], i["first_name"]), admins))), quote=True)
	await message.delete(revoke=True)

	for i in admins:
		await client.send_message(i["id"], "{}{}".format("@{}".format(i["username"]) if i["username"] is not None else "<a href=\"tg://user?id={}\">{}</a>".format(i["id"], i["first_name"]), text))

	logger.info("I sent {}{}\'s request to the competent admin.".format("{} ".format(message.from_user.first_name) if message.from_user.first_name is not None else "", "{} ".format(message.from_user.last_name) if message.from_user.last_name is not None else "")


@app.on_message(Filters.command("ads", prefixes="/") & Filters.user(admins_list) & Filters.private)
async def announces(client: Client, message: Message):
	# /ads <text>
	global config

	message.command.pop(0)

	# Checking if the command is correct
	if message.command is False:
		await res.split_reply_text(config, message, "The syntax is: <code>/ads &lt;text&gt;</code>.", quote=False)
		logger.info("{} have sent an incorrect /announces request.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))
		return

	# Retrieving the chats' list
	with connection.cursor() as cursor:
		cursor.execute("SELECT `id` FROM `Chats` WHERE `type` LIKE \'%group\';")
		chats = list(map(lambda n: n["id"], cursor.fetchall()))

	# Retrieving the ad
	text = " ".join(message.command)

	for i in chats:
		await client.send_message(i, text)

	logger.info("I\'ve answered to /ads because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_callback_query(Filters.chat(chats_list) & ~Filters.user(blacklist))
async def answer_inline_button(client: Client, callback_query: CallbackQuery):
	global config, connection

	# Retrieving the data of the CallbackQuery
	data = callback_query.data.split(" ... ")

	"""
		data[0] is ...
		data[1] is ...
		...
	"""

	# Retrieving the keyboard of the CallbackQuery
	keyboard = callback_query.message.reply_markup.inline_keyboard

	# Retrieving the text of the CallbackQuery
	text = callback_query.message.text

	# Checking if the CallbackQuery have the correct syntax
	if data[0] == "Booked":
		text = "Text"

		# Restructuring the InlineKeyboard
		keyboard[0] = [
			InlineKeyboardButton(" ... ", callback_data=" ... ")
		]
	elif data[0] == "Free":
		text = "Text"
		keyboard = list()

		# Restructuring the InlineKeyboard
		keyboard.append([
			InlineKeyboardButton(" ... ", callback_data=" ... ")
		])

	keyboard = InlineKeyboardMarkup(keyboard)

	# Checking if the output text can be longest then the maximum length
	output = await callback_query.edit_message_text(text[: config.get("message_max_length")], disable_web_page_preview=True)
	if len(text) >= config.get("message_max_length"):
		for i in range(1, len(text), config.get("message_max_length")):
			try:
				output = await res.split_reply_text(config, message, text[i : i + config.get("message_max_length")], quote=False, disable_web_page_preview=True)
			except FloodWait as e:
				asyncio.sleep(e.x)

	await callback_query.edit_message_reply_markup(None)
	await output.edit_reply_markup(keyboard)

	logger.info("I have answered to an Inline button.")


@app.on_message(Filters.service & Filters.chat(chats_list))
async def automatic_management_service(_, message: Message):
	global config, connection

	# Checking if the message is a new_chat_members message
	if message.new_chat_members is not None:
		# Retrieving the list of the spammer by Telegram
		to_delete = message.new_chat_members.copy()
		to_delete = list(map(lambda n: n.id, to_delete))

		message.new_chat_members = list(filter(lambda n: n.is_scam is not None and n.is_scam is False, message.new_chat_members))

		for i in message.new_chat_members:
			to_delete.remove(i.id)

		# Retrieving the list of the spammer by Combot Anti Spam
		tmp = message.new_chat_members.copy()

		for i in range(len(tmp)):
			# Downloading the user's informations
			response = requests.get(url="https://api.cas.chat/check?user_id={}".format(tmp[i].id))

			# Retrieving the user's informations
			result = response.json()

			# Checking if it's a spammer
			if result["ok"] is False:
				continue

			to_delete.append(message.new_chat_members.pop(i).id)

		if to_delete is True:
			for i in to_delete:
				await message.chat.kick_member(i.id)

	await message.delete(revoke=True)


@app.on_message(Filters.command(["ban", "banall", "unban", "kick"], prefixes="/") & Filters.user(admins_list) & Filters.chat(chats_list))
async def ban_hammer(client: Client, message: Message):
	# /ban
	# /banall
	# /unban <username>
	# /kick
	global chats_list, config

	command = message.command.pop(0)

	# Checking if the command is correct
	if command == "unban" and message.command is False:
		await res.split_reply_text(config, message, "The syntax is: <code>/unban &lt;username&gt;</code>.", quote=False)
		logger.info("{} have sent an incorrect /unban request.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))
		return
	elif message.reply_to_message is None:
		logger.info("{} have sent an incorrect /{} request.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id, command))
		return

	# Checking if the admin can ban or unban the members of the chat
	user = await message.chat.get_member(message.from_user.id)
	if user.can_restrict_members is False:
		await message.delete(revoke=True)
		return

	# Executing the command
	if command == "unban":
		user = await client.get_users(message.command.pop(0))
		await message.chat.unban_member(user.id)
	else:
		user = message.reply_to_message.from_user
		limits = 0

		if command == "kick":
			limits = 31

		await message.chat.kick_member(user.id, until_date=limits)

		# Checking if the player must be banned from all chats
		if command == "banall":
			for i in chats_list:
				if i == message.chat.id:
					continue

				await client.kick_chat_member(i, user.id)

	await res.split_reply_text(config, message, "I have {}ed @{}{}.".format(" {}n".format(command[: command.rindex("n")]) if command != "kick" else command, user.username, "from all chats" if command == "banall" else ""), quote=False)
	logger.info("I\'ve answered to /{} because of {}.".format(command, "@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.command(["blacklist", "unblacklist"], prefixes="/") & Filters.user(admins_list) & Filters.chat(chats_list))
async def blacklist(client: Client, message: Message):
	# /blacklist
	# /unblacklist <username>
	global admins_list, blacklist, chats_list, config, connection

	command = message.command.pop(0)

	# Checking if the command is correct
	if command == "unblacklist" and message.command is False:
		await res.split_reply_text(config, message, "The syntax is: <code>/unblacklist &lt;username&gt;</code>.", quote=False)
		logger.info("{} have sent an incorrect /unblacklist request.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))
		return
	elif message.reply_to_message is None:
		logger.info("{} have sent an incorrect /blacklist request.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))
		return

	# Executing the command
	if command == "unblacklist":
		user = await client.get_users(message.command.pop(0))

		blacklist.remove(user.id)

		for i in chats_list:
			await client.unban_chat_member(i, user.id)

		# Removing the user from the blacklist
		with connection.cursor() as cursor:
			cursor.execute("DELETE FROM `Blacklist` WHERE `id`=%(id)s;", {
				"id": user.id
			})
		connection.commit()
	else:
		user = message.reply_to_message.from_user

		admins_list.remove(user.id)

		blacklist.append(user.id)

		for i in chats_list:
			await client.kick_chat_member(i, user.id)

		# Adding the user to the blacklist
		with connection.cursor() as cursor:
			cursor.execute("DELETE FROM `Admins` WHERE `id`=%(id)s;", {
				"id": user.id
			})

			cursor.execute("INSERT INTO `Blacklist` (`id`) VALUES (%(id)s);", {
				"id": user.id
			})
		connection.commit()

	await res.split_reply_text(config, message, "I have {}ed @{}.".format(command, user.username), quote=False)
	logger.info("I\'ve answered to /{} because of {}.".format(command, "@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.command("check", prefixes="/") & Filters.user(config.get("creator")))
async def check_database(_, message: Message):
	# /check
	global admins_list, connection, chats_list

	with connection.cursor() as cursor:
		cursor.execute("SELECT * FROM `Admins`;")
		print("{}\n".format(cursor.fetchall()))
	print("{}\n".format(list(map(lambda n: "\t{} - {}\n".format(n, type(n)), admins_list))))

	with connection.cursor() as cursor:
		cursor.execute("SELECT * FROM `Chats`;")
		print("{}\n".format(cursor.fetchall()))
	print("{}\n".format(list(map(lambda n: "\t{} - {}\n".format(n, type(n)), chats_list))))

	print("\n\n")
	logger.info("I\'ve answered to /check because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.command("command1", prefixes="/") & Filters.private & ~Filters.user(blacklist))
async def command1(client: Client, message: Message):
	# /command1
	"""
		If the command has any arguments, it can be acceded at message.command parameter
		That parameter is a list with the first element equal to the command (message.command[0] == "command1")
	"""
	logger.info("I\'ve answered to /command1 because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.command("command2", prefixes="/") & Filters.private & ~Filters.user(blacklist))
async def command2(_, message: Message):
	# /command2
	keyboard=list()

	keyboard.append([
		KeyboardButton("Text")
	])
	keyboard.append([
		KeyboardButton("Text"),
		KeyboardButton("Text")
	])

	keyboard = ReplyKeyboardMarkup(keyboard=keyboard, resize_keyboard=True, one_time_keyboard=False)

	await message.reply_text("Text", reply_markup=keyboard)

	logger.info("I\'ve answered to /command2 because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.command("help", prefixes="/") & Filters.private & ~Filters.user(blacklist))
async def help(_, message: Message):
	# /help
	global admins_list, config

	commands = config.get("commands")

	# Filter the commands list in base at their domain
	if message.from_user.id != config.get("creator"):
		commands = list(filter(lambda n: n["domain"] != "creator", commands))
	if message.from_user.id not in admins_list:
		commands = list(filter(lambda n: n["domain"] != "admin", commands))

	await res.split_reply_text(config, message, "In this section you will find the list of the command of the bot.\n\t{}".format("\n\t".join(list(map(lambda n: "<code>/{}{}</code> - {}".format(n["name"], " {}".format(n["parameters"]) if n["parameters"] != "" else n["parameters"], n["description"])), commands))), quote=False)

	logger.info("I\'ve answered to /help because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.command("init", prefixes="/") & Filters.user(admins_list) & Filters.private)
async def initializing(client: Client, _):
	# /init
	global config

	# Scheduling the functions
	scheduler.add_job(update, IntervalTrigger(days=1, timezone="Europe/Rome"), kwargs={
		"client": client
	})

	# Setting the maximum message length
	max_length = await client.send(functions.help.GetConfig())
	config.set("message_max_length", max_length.message_length_max)

	# Retrieving the bot id
	bot = await client.get_users(config.get("bot_username"))
	config.set("bot_id", bot.id)

	logger.info("I\'ve answered to /init because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_inline_query(Filters.chat(chats_list) & ~Filters.user(blacklist))
async def inline(_, inline_query: InlineQuery):
	# Inline command
	query = inline_query.query.lower()
	response = list()
	keyboard = list()

	# Checking if the text of the query is correct
	if query == "text":
		title = "Text"
		url = "Text"
		description = "Text"

		response.append(InlineQueryResultArticle(title=title, input_message_content=InputTextMessageContent("Text", parse_mode="html", disable_web_page_preview=True), url=url, description=description))
	elif query == "text":
		title = "Text"
		url = "Text"
		description = "Text"

		keyboard.append([
			InlineKeyboardButton("Text", callback_data="Text", url="Text")
		])

		response.append(InlineQueryResultArticle(title=title, input_message_content=InputTextMessageContent("Text", parse_mode="html", disable_web_page_preview=True), url=url, description=description, reply_markup=keyboard))
	else:
		response.append(InlineQueryResultArticle(title="Unknown", input_message_content=InputTextMessageContent("List of word to use as keyword in the Inline Mode:\n\t<code>{}</code>".format("</code>\n\t<code>".join(keywords)), parse_mode="html")))

	await inline_query.answer(response, cache_time=1)
	logger.info("I sent the answer to the Inline Query of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.command("link", prefixes="/") & Filters.group & Filters.chat(chats_list) & ~Filters.user(blacklist))
async def link(client: Client, message: Message):
	# /link
	global config

	text = "@{} is the link to this chat.".format(message.chat.username) if message.chat.username is not None else ""

	if message.chat.username is None:
		chat = client.get_chat(message.chat.id)

		text = "This is the <a href=\"{}\">link</a> to this chat.".format(chat.invite_link)

	await res.split_reply_text(config, message, text, quote=False)
	logger.info("I\'ve answered to /link because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.command(["mute", "silence", "unmute", "unsilence"], prefixes="/") & Filters.user(admins_list) & Filters.group & Filters.chat(chats_list))
async def mute_hammer(client: Client, message: Message):
	# /mute [time]
	# /silence
	# /unmute
	# /unsilence
	global admins_list, config, connection

	command = message.command.pop(0)

	# Checking if the command is correct
	if "mute" in command and message.reply_to_message is None:
		logger.info("{} have sent an incorrect /{} request.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id, command))
		return

	permission = message.chat.permissions

	# Checking if the command is /mute or /silence
	if "un" not in command:
		permission = ChatPermission(can_send_messages=False, can_send_media_messages=False, can_send_stickers=False, can_send_animations=False, can_send_games=False, can_use_inline_bots=False, can_add_web_page_previews=False, can_send_polls=False, can_change_info=False, can_pin_messages=False)

	# Executing the command
	if "mute" in command:
		user = message.reply_to_message.from_user

		if user.id in admins_list:
			return

		limits = 0

		if command == "mute" and message.command is True:
			limits = message.command.pop(0)

		await message.chat.restrict_member(user.id, permissions, until_date=limits)
	else:
		for i in message.chat.iter_members():
			if i.user.id in admins_list:
				continue

			await message.chat.restrict_member(i.user.id, permission)

	await res.split_reply_text(config, message, "I have {}d {}.".format(command, "@{}".format(user.username) if "mute" in command else "all the users in the chat"), quote=False)
	logger.info("I\'ve answered to /{} because of {}.".format(command, "@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


def queue1(client: Client, ...):
	logger.info("I have done my job.")


def queue2(client: Client, ...):
	logger.info("I have done my job.")


@app.on_message(Filters.command("remove", prefixes="/") & (Filters.user(config.get("creator")) | Filters.channel))
async def remove_from_the_database(client: Client, message: Message):
	# /remove
	global admins_list, chats_list, config

	# Checking if the data are of a chat or of a user
	if message.reply_to_message is not None:
		# Checking if the user is authorized
		if message.reply_to_message.from_user.id not in admins_list:
			await res.split_reply_text(config, message, "You can\'t remove an admin that doesn't exists.", quote=False)
			logger.info("{} have sent an incorrect /remove request.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))
			return

		# Retrieving the data of the admin
		chat = message.reply_to_message.from_user

		# Removing the admin from the list
		admins_list.remove(chat.id)
	else:
		# Checking if the chat is in the list
		if message.chat.id not in chats_list:
			await res.split_reply_text(config, message, "The chat {} isn\'t present in the list of allowed chat.".format(message.chat.title), quote=False)
			logger.info("{} have sent an incorrect /remove request.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))
			return

		# Retrieving the data of the chat
		chat = message.chat

		# Deleting the message
		await message.delete(revoke=True)

		# Removing the chat from the list
		chats_list.remove(chat.id)

	# Removing the chat/user from the database
	with connection.cursor() as cursor:
		text = "Chat removed from the database."

		if cursor.execute("DELETE FROM `Chats` WHERE `id`=%(id)s;", {
			"id": chat.id
		}) == 0:
			cursor.execute("DELETE FROM `Admins` WHERE `id`=%(id)s;", {
				"id": chat.id
			})

			for i in chats_list:
				# Checking if the user is in the chat
				try:
					await client.get_chat_member(i, chat["id"])
				except ChannelInvalid:
					# Downgrading the player's privilege
					await client.promote_chat_member(i, chat["id"], can_change_info=False, can_post_messages=True, can_edit_messages=False, can_delete_messages=False, can_restrict_members=False, can_invite_users=True, can_pin_messages=False, can_promote_members=False)

			text = "Admin removed from the database."
	connection.commit()

	await res.split_reply_text(config, message, text, quote=False)
	logger.info("I\'ve answered to /remove because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.command("report", prefixes="/") & Filters.user(config.get("creator")) & Filters.private)
async def report(_, message: Message):
	# /report
	global config

	await res.split_reply_text(config, message, "\n".join(list(map(lambda n: "{} - {}".format(n["name"], n["description"]), config.get("commands")))), quote=False)

	logger.info("I\'ve answered to /report because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(Filters.text & Filters.private & ~Filters.user(blacklist))
async def split(client: Client, message: Message):
	if message.text == " ... ":
		pass
	elif message.text == " ... ":
		pass

		...

	else:
		pass


@app.on_message(Filters.command("start", prefixes="/") & Filters.private & ~Filters.user(blacklist))
async def start(client: Client, message: Message):
	# /start
	global config

	await res.split_reply_text(config, "Welcome @{}.\nThis bot ... ".format(message.from_user.username), quote=False)
	logger.info("I\'ve answered to /start because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


@app.on_message(res.unknown_filter(config) & Filters.private & ~Filters.user(blacklist))
async def unknown(_, message: Message):
	global config

	await res.split_reply_text(config, message, "This command isn\'t supported.", quote=False)
	logger.info("I managed an unsupported command.")


@app.on_message(Filters.command("update", prefixes="/") & Filters.user(config.get("creator")) & Filters.private)
async def update(client: Client, message: Message = None):
	# /update
	global admins_list, chats_list, connection

	chats = await client.get_users(admins_list)

	# Retrieving the list of deleted accounts
	to_delete = chats.copy()
	to_delete = list(map(lambda n: n.id, to_delete))

	chats = list(filter(lambda n: n.is_deleted is not None and n.is_deleted is False, chats))
	chats = list(filter(lambda n: n.is_scam is not None and n.is_scam is False, chats))

	for i in chats:
		to_delete.remove(i.id)

	# Retrieving the list of the spammer by Combot Anti Spam
	tmp = chats.copy()

	for i in range(len(tmp)):
		# Downloading the user's informations
		response = requests.get(url="https://api.cas.chat/check?user_id={}".format(tmp[i].id))

		# Retrieving the user's informations
		result = response.json()

		# Checking if it's a spammer
		if result["ok"] is False:
			continue

		to_delete.append(chats.pop(i).id)

	chats = list(map(lambda n: n.__dict__, chats))

	with connection.cursor() as cursor:
		# Removing inutil informations
		for i in to_delete:
			cursor.execute("DELETE FROM `Admins` WHERE `id`=%(id)s;", {
				"id": i
			})

		for i in chats:
			# Removing inutil informations
			i.pop("_client", None)
			i.pop("_", None)
			i.pop("photo", None)
			i.pop("restrictions", None)
			i.pop("status", None)
			i.pop("last_online_date", None)
			i.pop("next_offline_date", None)
			i.pop("dc_id", None)
			i.pop("is_self", None)
			i.pop("is_contact", None)
			i.pop("is_mutual_contact", None)
			i.pop("is_deleted", None)
			i.pop("is_bot", None)
			i.pop("is_verified", None)
			i.pop("is_restricted", None)
			i.pop("is_scam", None)
			i.pop("is_support", None)
			i.pop("language_code", None)

			# Updating the database
			cursor.execute("UPDATE `Admins` SET `first_name`=%(first_name)s, `last_name`=%(last_name)s, `username`=%(username)s, `phone_number`=%(phone_number)s WHERE `id`=%(id)s;", i)
	connection.commit()

	chats = list()
	for i in chats_list:
		try:
			chats.append(await client.get_chat(i))
		except FloodWait as e:
			await asyncio.sleep(e.x)

	chats = list(map(lambda n: n.__dict__, chats))

	with connection.cursor() as cursor:
		for i in chats:
			# Removing inutil informations
			i.pop("_client", None)
			i.pop("_", None)
			i.pop("photo", None)
			i.pop("description", None)
			i.pop("pinned_message", None)
			i.pop("sticker_set_name", None)
			i.pop("can_set_sticker_set", None)
			i.pop("members_count", None)
			i.pop("restrictions", None)
			i.pop("permissions", None)
			i.pop("distance", None)
			i.pop("is_verified", None)
			i.pop("is_restricted", None)
			i.pop("is_scam", None)
			i.pop("is_support", None)

			# Updating the database
			cursor.execute("UPDATE `Chats` SET `type`=%(type)s, `title`=%(title)s, `username`=%(username)s, `first_name`=%(first_name)s, `last_name`=%(last_name)s, `invite_link`=%(invite_link)s WHERE `id`=%(id)s;", i)
	connection.commit()

	for i in chats_list:
		# Retrieving the list of chat members
		members = await client.iter_chat_members(i)
		members = list(members)

		# Retrieving the users from the list
		members = list(map(lambda n: n.user, members))

		members = list(filter(lambda n: n.is_bot is not None and n.is_bot is False, members))

		# Retrieving the list of deleted accounts
		to_delete = members.copy()
		to_delete = list(map(lambda n: n.id, to_delete))

		members = list(filter(lambda n: n.is_deleted is not None and n.is_deleted is False, members))
		members = list(filter(lambda n: n.is_scam is not None and n.is_scam is False, members))
		members = list(map(lambda n: n.id, members))

		for j in members:
			to_delete.remove(j)

		# Retrieving the list of the spammer by Combot Anti Spam
		tmp = members.copy()

		for i in range(len(tmp)):
			# Downloading the user's informations
			response = requests.get(url="https://api.cas.chat/check?user_id={}".format(tmp[i].id))

			# Retrieving the user's informations
			result = response.json()

			# Checking if it's a spammer
			if result["ok"] is False:
				continue

			to_delete.append(members.pop(i).id)

		# Removing inutil informations
		if to_delete is True:
			for j in to_delete:
				await message.chat.kick_member(j)

	logger.info("I\'ve answered to /update because of {}.".format("@{}".format(message.from_user.username) if message.from_user.username is not None else message.from_user.id))


logger.info("Client initializated\nSetting the markup syntax ...")
app.set_parse_mode("html")

logger.info("Set the markup syntax\nStarted serving ...")
scheduler.start()
app.run()
connection.close()
