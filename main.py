import json

import schedule
from pyrogram import CallbackQuery, Client, Filters, InlineKeyboardButton, InlineKeyboardMarkup, \
					 InlineQuery, InlineQueryResultArticle, KeyboardButton, Message, ReplyKeyboardMarkup

from modules import Constants

commands = list(["addadmin",
				 "command1",
				 "command2",
				 "command3",
				 "help",
				 "removeadmin",
				 "report",
				 "start"
				])
constants = Constants.Constants()
initialLog = list(["Initializing the Admins ...", "Admins initializated\nSetting the admins list ...",
				   "Admins setted\nSetting the users list ...", "Users initializated\nInitializing the Client ..."])
scheduler = schedule.default_scheduler
"""
	Initializing the Admins ...
"""
constants.loadCreators()
"""
	Admins initializated
	Setting the admins list ...
"""
adminsIdList = set()
i = constants.admins.to_json(orient="columns")
i = i[len("{\"id\":{"):i.index("}")]
i = i.split(",")
i = list(map(lambda n: n.split(":"), i))
i = list(map(lambda n: dict({n[0]: n[1]}), i))
i = list(map(lambda n: list(n.values()), i))
list(map(lambda n: list(map(lambda m: adminsIdList.add(int(m)), n)), i))
adminsIdList = list(adminsIdList)
"""
	Admins setted
	Setting the users list ...
"""
userIdList = set()
i = constants.users.to_json(orient="columns")
i = i[len("{\"id\":{"):i.index("}")]
i = i.split(",")
i = list(map(lambda n: n.split(":"), i))
i = list(map(lambda n: dict({n[0]: n[1]}), i))
i = list(map(lambda n: list(n.values()), i))
list(map(lambda n: list(map(lambda m: userIdList.add(int(m)), n)), i))
userIdList = list(userIdList)
"""
	Users initializated
	Initializing the Client ...
"""
app = Client(session_name=constants.username, api_id=constants.id, api_hash=constants.hash, bot_token=constants.token)


@app.on_message(
		Filters.command("addadmin", prefixes=list(["/"])) & Filters.user(adminsIdList) & Filters.private)
def addAdmin(client: Client, message: Message):
    """
        /addadmin [username]
    """
	global adminsIdList, constants

	user = client.get_users(message.command.pop(1))
	text = "The user {}".format("{} ".format(user.first_name) if user.first_name is not None else "")
	text += "{}is already present in the list of allowed chat.".format("{} ".format(user.last_name) if user.last_name is not None else "")
	if user.id not in adminsIdList:
		"""
			Adding the chat to the database
		"""
		userDict = user.__dict__
		try:
			del userDict["_client"]
		except KeyError:
			pass
		try:
			del userDict["photo"]
		except KeyError:
			pass
		try:
			del userDict["restrictions"]
		except KeyError:
			pass
		try:
			del userDict["status"]
		except KeyError:
			pass
		try:
			del userDict["last_online_date"]
		except KeyError:
			pass
		try:
			del userDict["next_offline_date"]
		except KeyError:
			pass
		try:
			del userDict["dc_id"]
		except KeyError:
			pass
		constants.admins = list([userDict])
		text = "I added {}".format("{} ".format(user.first_name) if user.first_name is not None else "")
		text += "{}to the list of allowed chat at {}.".format("{} ".format(user.last_name) if user.last_name is not None else "", constants.now())
	log(client, text)


@app.on_callback_query(Filters.user(userIdList))
def answerInlineButton(client: Client, callback_query: CallbackQuery):
    global constants

	log(client, "@{} has pressed an Inline button at {}.".format(callback_query.from_user.username, constants.now()))
	keyboard = list()
	text = ""
	if callback_query.data.lower() == "text":
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
		text = "Text"
	elif callback_query.data.lower() == "text":
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
		text = "Text"
	elif callback_query.data.lower() == "text":
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text"), ...]))
		text = "Text"
	elif callback_query.data.lower() == "text":
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text", switch_inline_query="Text"), ...]))
		text = "Text"
	else:
		keyboard.append(list([InlineKeyboardButton("Text", callback_data="Text", url="Text", switch_inline_query_current_chat="Text"), ...]))
		text = "Text"
	callback_query.answer(text, show_alert=True)
	callback_query.edit_message_text(text, disable_web_page_preview=True)
	callback_query.edit_message_reply_markup(InlineKeyboardMarkup(keyboard))
	log(client, "I have answered to an Inline button at {}.".format(constants.now()))


@app.on_message(Filters.service)
def automaticRemovalStatus(client: Client, message: Message):
	"""
		Removing the status message
	"""
	message.delete(revoke=True)


@app.on_message(
		Filters.command("command1", prefixes=list(["/"])) & Filters.user(userIdList) & Filters.private)
def command1(client: Client, message: Message):
    global constants

	log(client, "I have answered to /command1 at {} because of @{}.".format(constants.now(), message.from_user.username))
	"""
		If the command has any arguments, it can be acceded at message.command parameter
		That parameter is a list with the first element equal to the command (message.command(0) == "command1")
	"""


@app.on_message(
		Filters.command("command2", prefixes=list(["/"])) & Filters.user(userIdList) & Filters.private)
def command2(client: Client, message: Message):
    """
        /command2
    """
    global constants, scheduler

	log(client, "I have answered to /command2 at {} because of @{}.".format(constants.now(), message.from_user.username))
	for i in range(constants.users.shape[0]):
		if constants.users.at[i, "id"] == message.from_user.id and constants.users.at[i, "flag"] is False:
			scheduler.every().day.at("14:00").do(queue1, client=client, ...)
			log(client, "@{} activated the Job Queue that DO SOMETHING at {}.".format(message.from_user.username, constants.now()))
			scheduler.every().monday.at("2:00").do(queue2, client=client, ...)
			log(client, "@{} activated the Job Queue that DO SOMETHING ELSE at {}.".format(message.from_user.username, constants.now()))

			...

			constants.users.at[i, "flag"] = True
			break


@app.on_message(
		Filters.command("command3", prefixes=list(["/"])) & Filters.user(userIdList) & Filters.private)
def command3(client: Client, message: Message):
    """
        Command that reply with a keyboard
    """
    global constants

	log(client, "I have answered to /command3 at {} because of @{}.".format(constants.now(), message.from_user.username))
	keyboard = ReplyKeyboardMarkup(keyboard=list([list([KeyboardButton("Text"), ...]), ...]), resize_keyboard=True, one_time_keyboard=False)
	message.reply_text("Text", reply_markup=keyboard)


@app.on_message(
		Filters.command("help", prefixes=list(["/"])) & Filters.user(userIdList) & Filters.private)
def help(client: Client, message: Message):
    """
        /help
    """
    global commands, constants

	message.reply_text("In this section you will find the list of the command of the bot.\n\t{}.".format("\n\t".join(commands)))
	log(client, "I helped @" + message.from_user.username + " at " + constants.now() + ".")


@app.on_inline_query(Filters.user(userIdList))
def inline(client: Client, inline_query: InlineQuery):
    """
        Inline command
    """
    global constants

	results = list()
	keyboard = list()
	queryID = ""
	title = ""
	URL = ""
	description = ""
	text = ""
	"""
		Checking if the text of the query is correct
	"""
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
	"""
		Sending the output
	"""
	inline_query.answer(results, switch_pm_text="Text", switch_pm_parameter="Text")
	log(client, "I sent the answer to the Inline Query of @" + inline_query.from_user.username + ".")


def log(client: Client = None, logging: str = ""):
	global constants, initialLog

	if client is not None:
		if initialLog is not None:
			for msg in initialLog:
				client.send_message(constants.log, msg)
			initialLog = None
		client.send_message(constants.log, logging)
	else:
		initialLog.append(logging)


def queue1(client: Client, ...):
    """
        Do a Job in the Job Queue
    """
	log(client, "I have done my job at {}.".format(constants.now()))


def queue2(client: Client, ...):
    """
        Do a Job in the Job Queue
    """
	log(client, "I have done my job at {}.".format(constants.now()))


@app.on_message(
		Filters.command("removeadmin", prefixes=list(["/"])) & Filters.user(adminsIdList) & Filters.private)
def removeAdmin(client: Client, message: Message):
    """
        /removeadmin [username]
    """
    global constants

	message.command.pop(0)
	if len(message.command) != 1:
		message.reply_text("The syntax is: `/removeadmin [username]`.")
		log(client,
			"I helped @{} with /removeadmin at {}.".format(message.from_user.username, constants.now()))
		return
	for i in range(constants.admins.shape[0]):
		if constants.admins.at[i, "username"].lower() == message.command[0].lower():
			if constants.creator == constants.admins.at[i, "id"]:
				message.reply_text("You can\'t remove the creator of the bot from the admin list.")
				log(client, "@{} tried to remove you as admin at {}.".format(message.from_user.username, constants.now()))
				return
			else:
				message.command[0] = constants.admins.at[i, "username"]
				constants.admins.drop(list([i]))
				break
	constants.admins.reset_index(drop=True)
	constants.save()
	message.reply_text("Admin removed.")
	log(client, "I removed an admin (@{}) at @{}\'s request at {}.".format(message.command[0], message.from_user.username, constants.now()))


@app.on_message(
		Filters.command("report", prefixes=list(["/"])) & Filters.user(constants.creator) & Filters.private)
def report(client: Client, message: Message):
    """
        /report
    """
    global commands, constants

	text = commands.copy()
	for i in text:
		i += " - Description"
	message.reply_text("\n".join(text))
	log(client, "I send a report to @{} at {}.".format(message.from_user.username, constants.now()))


@app.on_message(Filters.text & Filters.user(userIdList) & Filters.private)
def split(client: Client, message: Message):
    if message.text is not None:
        if message.text == " ... ":
            pass
        elif message.text == " ... ":
            pass

            ...

        else:
            pass


@app.on_message(
		Filters.command("start", prefixes=list(["/"])) & Filters.user(userIdList) & Filters.private)
def start(client: Client, message: Message):
    """
        /start
    """
    global constants

	message.reply_text("Welcome @{}.\nThis bot ...".format(message.from_user.username))
	log(client, "I started at {} because of @{}.".format(constants.now(), message.from_user.username))


def unknownFilter():
	global commands

	def func(flt, message: Message):
		text = message.text
		if text:
			message.matches = list(flt.p.finditer(text)) or None
			if bool(message.matches) is False and text.startswith("/") is True and len(text) > 1:
				return True
		return False
	return Filters.create(func, "UnknownFilter", p=re.compile("/{}".format("|/".join(commands)), 0))


@app.on_message(unknownFilter() & Filters.user(userIdList) & Filters.private)
def unknown(client: Client, message: Message):
	global constants

	message.reply_text("This command isn\'t supported.")
	log(client, "I managed an unsupported command at {}.".format(constants.now()))


log(logging="Client initializated\nSetting the markup syntax ...")
app.set_parse_mode("html")
log(logging="Setted the markup syntax\nSetting the Job Queue ...")
log(logging="Setted the Job Queue\nStarted serving ...")
with app:
	while True:
		scheduler.run_pending()
