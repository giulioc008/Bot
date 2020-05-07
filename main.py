from apscheduler.schedulers.asyncio import AsyncIOScheduler
from apscheduler.triggers.interval import IntervalTrigger
import asyncio
import logging as logger
import pymysql
from pyrogram import CallbackQuery, Client, Filters, InlineKeyboardButton, InlineKeyboardMarkup, InlineQuery, InlineQueryResultArticle, KeyboardButton, Message, ReplyKeyboardMarkup
from pyrogram.errors import FloodWait
import re
from res.configurations import Configurations

def stopFilterCommute(self):
	self.flag = not self.flag


adminsIdList = list()
chatIdList = list()

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
config.set("creator", int(os.environ.pop("creator", None)))

connection = pymysql.connect(
	host=config.get("database")["host"],
	user=config.get("database")["username"],
	password=config.get("database")["password"],
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
stopFilter = Filters.create(lambda self, _: self.flag, flag=True, commute=stopFilterCommute)

with connection.cursor() as cursor:
	logger.info("Setting the admins list ...")
	cursor.execute("SELECT `id` FROM `Admins`")

	for i in cursor.fetchall():
		adminsIdList.append(i["id"])
	logger.info("Admins setted\nSetting the chats list ...")
	cursor.execute("SELECT `id` FROM `Chats`")

	for i in cursor.fetchall():
		chatIdList.append(i["id"])

logger.info("Chats initializated\nInitializing the Client ...")
app = Client(session_name=config.get("bot_username"), api_id=config.get("app_id"), api_hash=config.get("app_hash"), bot_token=config.get("bot_token"), lang_code="it")


async def split_edit_text(message: Message, text: str):
	"""
		A coroutine that edits the text of a message; if text is too long sends more messages.
		:param message: Message to edit
		:param text: Text to insert
		:return: None
	"""
	global config

	await message.edit_text(text[:config.get("message_max_length")])
	if len(text) >= config.get("message_max_length"):
		for i in range(1, len(text), config.get("message_max_length")):
			try:
				await message.reply_text(text[i:i + config.get("message_max_length")], quote=False)
			except FloodWait as e:
				await asyncio.sleep(e.x)


async def split_reply_text(message: Message, text: str):
	"""
		A coroutine that reply to a message; if text is too long sends more messages.
		:param message: Message to reply
		:param text: Text to insert
		:return: None
	"""
	global config

	await message.reply_text(text[:config.get("message_max_length")], quote=False)
	if len(text) >= config.get("message_max_length"):
		for i in range(1, len(text), config.get("message_max_length")):
			try:
				await message.reply_text(text[i:i + config.get("message_max_length")], quote=False)
			except FloodWait as e:
				await asyncio.sleep(e.x)


@app.on_message(Filters.command("add", prefixes="/") & (Filters.user(config.get("creator")) | Filters.channel) & stopFilter)
async def add_to_the_database(client: Client, message: Message):
	# /add
	global adminsIdList, chatIdList, config, stopFilter

	await stopFilter.commute()
	# Checking if the message arrive from a channel and, if not, checking if the user that runs the command is allowed
	if message.from_user is not None and message.from_user.id != config.get("creator"):
		await stopFilter.commute()
		return

	lists = chatIdList
	text = "The chat {} is already present in the list of allowed chat.".format(chat.title)

	# Checking if the data are of a chat or of a user
	if message.reply_to_message is not None:
		chat = await client.get_users(message.reply_to_message.from_user.id)
		chat = chat.__dict__
		lists = adminsIdList
		text = "The user @{} is already an admin.".format(chat["username"])
	else:
		chat = await client.get_chat(message.chat.id)
		chat = chat.__dict__

		# Deleting the message
		await message.delete(revoke=True)

	# Checking if the chat/user is in the list
	if chat["id"] in lists:
		await stopFilter.commute()
		logger.info(text)
		return

	# Adding the chat/user to the database
	lists.append(chat["id"])

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

	with connection.cursor() as cursor:
		if config.get("creator") in lists:
			cursor.execute("INSERT INTO `Admins` (`id`, `first_name`, `last_name`, `username`, `language_code`, `phone_number`) VALUES (%(id)s, %(first_name)s, %(last_name)s, %(username)s, %(language_code)s, %(phone_number)s)", chat)
			await message.chat.promote_member(chat["id"], can_change_info=True, can_post_messages=True, can_edit_messages=False, can_delete_messages=True, can_restrict_members=True, can_invite_users=True, can_pin_messages=True, can_promote_members=False)
			text = "I added {}{} to the list of allowed user.".format("{} ".format(chat["first_name"]) if chat["first_name"] is not None else "", "{} ".format(chat["last_name"]) if chat["last_name"] is not None else "")
		else:
			cursor.execute("INSERT INTO `Chats` (`id`, `type`, `title`, `username`, `first_name`, `last_name`, `invite_link`) VALUES (%(id)s, %(type)s, %(title)s, %(username)s, %(first_name)s, %(last_name)s, %(invite_link)s)", chat)
			text = "I added {} to the list of allowed chat.".format(chat["title"])
		connection.commit()

	await stopFilter.commute()
	logger.info(text)


@app.on_callback_query()
async def answerInlineButton(_, callback_query: CallbackQuery):
	keyboard = list()
	text = ""

	if callback_query.data.lower() == "text":
		keyboard.append([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...])
		text = "Text"
	elif callback_query.data.lower() == "text":
		keyboard.append([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...])
		keyboard.append(([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...])
		text = "Text"
	elif callback_query.data.lower() == "text":
		keyboard.append([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...])
		keyboard.append([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...])
		keyboard.append([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...])
		text = "Text"
	elif callback_query.data.lower() == "text":
		keyboard.append([InlineKeyboardButton("Text", callback_data="Text", url="Text", switch_inline_query="Text"), ...])
		text = "Text"
	else:
		keyboard.append([InlineKeyboardButton("Text", callback_data="Text", url="Text", switch_inline_query_current_chat="Text"), ...])
		text = "Text"

	await callback_query.answer(text, show_alert=True)

	await callback_query.edit_message_text(text, disable_web_page_preview=True)
	await callback_query.edit_message_reply_markup(InlineKeyboardMarkup(keyboard))

	logger.info("I have answered to an Inline button.")


@app.on_message(Filters.service)
def automaticRemovalStatus(_, message: Message):
	await message.delete(revoke=True)


@app.on_message(Filters.command("command1", prefixes="/") & Filters.private)
async def command1(client: Client, message: Message):
	# /command1
	"""
		If the command has any arguments, it can be acceded at message.command parameter
		That parameter is a list with the first element equal to the command (message.command[0] == "command1")
	"""
	logger.info("I have answered to /command1 because of @{}.".format(message.from_user.username))


@app.on_message(Filters.command("command2", prefixes="/") & Filters.private)
async def command2(_, message: Message):
	# /command2
	# Command that reply with a keyboard
	keyboard=list()

	keyboard.append([KeyboardButton("Text"), ...])
	keyboard.append([KeyboardButton("Text"), ...])
	keyboard.append([KeyboardButton("Text"), ...])
	keyboard.append([KeyboardButton("Text"), ...])
	keyboard.append([KeyboardButton("Text"), ...])

	keyboard = ReplyKeyboardMarkup(keyboard=keyboard, resize_keyboard=True, one_time_keyboard=False)

	await message.reply_text("Text", reply_markup=keyboard)

	logger.info("I have answered to /command2 because of @{}.".format(message.from_user.username))


@app.on_message(Filters.command("help", prefixes="/") & Filters.private)
async def help(_, message: Message):
	# /help
	global config

	commands = config.get("commands")

	# Filter the commands list in base at their domain
	if message.from_user.id not in adminsIdList:
		commands = list(filter(lambda n: n["domain"] != "admin", commands))
	if message.from_user.id != config.get("creator"):
		commands = list(filter(lambda n: n["domain"] != "creator", commands))

	await split_reply_text(message, "In this section you will find the list of the command of the bot.\n\t{}".format("\n\t".join(list(map(lambda n: "<code>/{} {}</code> - {}".format(n["name"], n["parameters"], n["description"])), commands))))

	logger.info("I sent the help.")


@app.on_message(Filters.command("init", prefixes="/") & Filters.user(adminsIdList) & Filters.private)
async def initializing(client: Client, _):
	global config

	max_length = await client.send(GetConfig())
	config.set("message_max_length", max_length.message_length_max)


@app.on_inline_query(Filters.user(userIdList))
async def inline(_, inline_query: InlineQuery):
	# Inline command
	results = list()
	keyboard = list()
	queryID = ""
	title = ""
	URL = ""
	description = ""
	text = ""

	# Checking if the text of the query is correct
	if inline_query.query.lower() == "text":
		queryID = "Text"
		title = "Text"
		URL = "Text"
		description = "Text"

		text = InputTextMessageContent("Text", disable_web_page_preview=True)

		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
	elif inline_query.query.lower() == "text":
		queryID = "Text"
		title = "Text"
		URL = "Text"
		description = "Text"

		text = InputTextMessageContent("Text", disable_web_page_preview=True)

		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
	elif inline_query.query.lower() == "text":
		queryID = "Text"
		title = "Text"
		URL = "Text"
		description = "Text"

		text = InputTextMessageContent("Text", disable_web_page_preview=True)

		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
	elif inline_query.query.lower() == "text":
		queryID = "Text"
		title = "Text"
		URL = "Text"
		description = "Text"

		text = InputTextMessageContent("Text", disable_web_page_preview=True)

		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text", switch_inline_query="Text"), ...]))
	else:
		queryID = "Text"
		title = "Text"
		URL = "Text"
		description = "Text"

		text = InputTextMessageContent("Text", disable_web_page_preview=True)

		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text", switch_inline_query_current_chat="Text"), ...]))
	keyboard=InlineKeyboardMarkup(keyboard)

	results.append(InlineQueryResultArticle(title=title, input_message_content=text, id=queryID, url=URL, description=description, reply_markup=keyboard))
	results.append(InlineQueryResultArticle(title=title, input_message_content=text, id=queryID, url=URL, description=description, reply_markup=keyboard))

	await inline_query.answer(results, switch_pm_text="Text", switch_pm_parameter="Text")
	logger.info("I sent the answer to the Inline Query of @{}.".format(inline_query.from_user.username))


def queue1(client: Client, ...):
	"""
		Do a Job in the Job Queue
	"""
	logger.info("I have done my job.")


def queue2(client: Client, ...):
	"""
		Do a Job in the Job Queue
	"""
	logger.info("I have done my job.")


@app.on_message(Filters.command("remove", prefixes="/") & (Filters.user(config.get("creator")) | Filters.channel) & stopFilter)
async def remove_from_the_database(_, message: Message):
	# /remove
	global adminsIdList, chatIdList, config, stopFilter

	await stopFilter.commute()

	# Checking if the message arrive from a channel and, if not, checking if the user that runs the command is allowed
	if message.from_user is not None and message.from_user.id != config.get("creator"):
		await stopFilter.commute()
		return

	lists = chatIdList
	title = message.chat.title
	text = "The chat {} hasn\'t been removed from the list of allowed chat.".format(title)

	# Checking if the data are of a chat or of a user
	if message.reply_to_message is not None:
		ids = message.reply_to_message.from_user.id
		lists = adminsIdList
		text = "The user @{} hasn\'t been removed from the admins list.".format(message.reply_to_message.from_user.username)
	else:
		ids = message.chat.id
		# Deleting the message
		await message.delete(revoke=True)

	# Checking if the chat/user is in the list
	if ids not in lists:
		await stopFilter.commute()
		logger.info(text)
		return

	# Removing the chat/user from the database
	lists.remove(ids)

	with connection.cursor() as cursor:
		if config.get("creator") in lists:
			cursor.execute("DELETE FROM `Admins` WHERE `id`=%(id)s", {"id": ids})
			await message.chat.restrict_member(ids, can_send_messages=True, can_send_media_messages=True, can_send_other_messages=True, can_add_web_page_previews=True, can_send_polls=True, can_change_info=False, can_invite_users=True, can_pin_messages=False)
			text = "The user @{} has been removed from the admins list.".format(message.reply_to_message.from_user.username)
		else:
			cursor.execute("DELETE FROM `Chats` WHERE `id`=%(id)s", {"id": ids})
			text = "The chat {} has been removed from the list of allowed chat.".format(title)
		connection.commit()

	await stopFilter.commute()
	logger.info(text)


@app.on_message(Filters.command("report", prefixes="/") & Filters.user(config.get("creator")) & Filters.private)
async def report(_, message: Message):
	# /report
	global config

	await message.reply_text("\n".join(list(map(lambda n: "{} - {}".format(n["name"], n["description"]), config.get("commands")))))

	logger.info("I send a report.")


@app.on_message(Filters.text & Filters.private)
async def split(client: Client, message: Message):
    if message.text is None:
		return

	if message.text == " ... ":
		pass
	elif message.text == " ... ":
		pass

		...

	else:
		pass


@app.on_message(Filters.command("start", prefixes="/") & Filters.private)
async def start(_, message: Message):
	# /start
	await message.reply_text("Welcome @{}.\nThis bot ...".format(message.from_user.username))
	logger.info("I started because of @{}.".format(message.from_user.username))


@app.on_message(Filters.command("update", prefixes="/") & Filters.user(adminsIdList) & Filters.private & stopFilter)
async def updateDatabase(client: Client, message: Message = None):
	# /update
	global adminsIdList, chatIdList, connection, config, stopFilter

	await stopFilter.commute()

	# Checking if the updating was programmed or not
	if message is not None:
		await message.delete(revoke=True)

	# Updating the admin's database
	adminsIdList.remove(config.get("creator"))
	chats = await client.get_users(adminsIdList)
	adminsIdList.append(config.get("creator"))
	await chats.append(client.get_me())
	chats = list(map(lambda n: n.__dict__, chats))

	with connection.cursor() as cursor:
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
			# Updating the admins' database
			cursor.execute("UPDATE `Admins` SET `first_name`=%(first_name)s, `last_name`=%(last_name)s, `username`=%(username)s, `language_code`=%(language_code)s, `phone_number`=%(phone_number)s WHERE `id`=%(id)s", i)
		connection.commit()

	# Updating the chats' database
	chats = list()
	for i in chatIdList:
		try:
			await chats.append(client.get_chat(i).__dict__)
		except FloodWait as e:
			await asyncio.sleep(e.x)

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
			# Updating the chats' database
			cursor.execute("UPDATE `Chats` SET `type`=%(type)s, `title`=%(title)s, `username`=%(username)s, `first_name`=%(first_name)s, `last_name`=%(last_name)s, `invite_link`=%(invite_link)s WHERE `id`=%(id)s", i)
		connection.commit()

	await stopFilter.commute()
	logger.info("I have updated the database.")


def unknownFilter():
	global config

	def func(flt, message: Message):
		text = message.text
		if text:
			message.matches = list(flt.p.finditer(text)) or None
			if bool(message.matches) is False and text.startswith("/") is True and len(text) > 1:
				return True
		return False

	commands = list(map(lambda n: n["name"], config.get("commands")))

	return Filters.create(func, "UnknownFilter", p=re.compile("/{}".format("|/".join(commands)), 0))


@app.on_message(unknownFilter() & Filters.private)
async def unknown(_, message: Message):
	await message.reply_text("This command isn\'t supported.")
	logger.info("I managed an unsupported command.")


logger.info("Client initializated\nSetting the markup syntax ...")
app.set_parse_mode("html")

logger.info("Set the markup syntax\nSetting the Job Queue ...")
scheduler.add_job(updateDatabase, IntervalTrigger(days=1, timezone="Europe/Rome"), kwargs={"client": app})

logger.info("Set the Job Queue\nStarted serving ...")
scheduler.start()
app.run()
connection.close()
