import logging as logger
import re
import asyncio
import schedule
from pyrogram import CallbackQuery, Client, Filters, InlineKeyboardButton, InlineKeyboardMarkup, InlineQuery, InlineQueryResultArticle, KeyboardButton, Message, ReplyKeyboardMarkup

from modules import Constants

commands = list(["addadmin", "command1", "command2", "command3", "help", "removeadmin", "report", "start"])
constants = Constants.Constants()
logger.basicConfig(filename="{}{}.log".format(constants.databasePath, constants.username), datefmt="%d/%m/%Y %H:%M:%S", format="At %(asctime)s was logged the event:\t%(levelname)s - %(message)s", level=logger.INFO)
scheduler = schedule.default_scheduler
logger.info("Initializing the Admins ...")
constants.loadCreators()
logger.info("Admins initializated\nSetting the admins list ...")
adminsIdList = set()
i = constants.admins.to_json(orient="columns")
i = i[len("{\"id\":{"):i.index("}")]
i = i.split(",")
i = list(map(lambda n: n.split(":"), i))
i = list(map(lambda n: dict({n[0]: n[1]}), i))
i = list(map(lambda n: list(n.values()), i))
list(map(lambda n: list(map(lambda m: adminsIdList.add(int(m)), n)), i))
adminsIdList = list(adminsIdList)
logger.info("Admins setted\nSetting the users list ...")
userIdList = set()
i = constants.users.to_json(orient="columns")
i = i[len("{\"id\":{"):i.index("}")]
i = i.split(",")
i = list(map(lambda n: n.split(":"), i))
i = list(map(lambda n: dict({n[0]: n[1]}), i))
i = list(map(lambda n: list(n.values()), i))
list(map(lambda n: list(map(lambda m: userIdList.add(int(m)), n)), i))
userIdList = list(userIdList)
logger.info("Users initializated\nInitializing the Client ...")
app = Client(session_name=constants.username, api_id=constants.id, api_hash=constants.hash, bot_token=constants.token)


@app.on_message(Filters.command("addadmin", prefixes=list(["/"])) & Filters.user(adminsIdList) & Filters.private)
async def addAdmin(client: Client, message: Message):
	"""
		/addadmin <username>
	"""
	global adminsIdList

	await user = client.get_users(message.command.pop(1))
	text = "@{} is already present in the admin list.".format(user.username))
	if user.id in adminsIdList:
		return
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
	text = "I added @{} to the admins database.".format(user.username)
    logger.info(text)


@app.on_callback_query(Filters.user(userIdList))
async def answerInlineButton(_, callback_query: CallbackQuery):
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
	await callback_query.answer(text, show_alert=True)
	await callback_query.edit_message_text(text, disable_web_page_preview=True)
	await callback_query.edit_message_reply_markup(InlineKeyboardMarkup(keyboard))
	logger.info("I have answered to an Inline button.")


@app.on_message(Filters.service)
def automaticRemovalStatus(_, message: Message):
	await message.delete(revoke=True)


@app.on_message(Filters.command("command1", prefixes=list(["/"])) & Filters.user(userIdList) & Filters.private)
async def command1(client: Client, message: Message):
	"""
		If the command has any arguments, it can be acceded at message.command parameter
		That parameter is a list with the first element equal to the command (message.command[0] == "command1")
	"""
	logger.info("I have answered to /command1 because of @{}.".format(message.from_user.username))


@app.on_message(Filters.command("command2", prefixes=list(["/"])) & Filters.user(userIdList) & Filters.private)
async def command2(_, message: Message):
	"""
		/command2
	"""
	global constants, scheduler

	for i in range(constants.users.shape[0]):
		if constants.users.at[i, "id"] == message.from_user.id and constants.users.at[i, "flag"] is False:
			scheduler.every().day.at("14:00").do(queue1, client=client, ...)
			logger.info("@{} activated the Job Queue that DO SOMETHING.".format(message.from_user.username))
			scheduler.every().monday.at("2:00").do(queue2, client=client, ...)
			logger.info("@{} activated the Job Queue that DO SOMETHING ELSE.".format(message.from_user.username))

			...

			constants.users.at[i, "flag"] = True
			break
	logger.info("I have answered to /command2 ecause of @{}.".format(message.from_user.username))


@app.on_message(Filters.command("command3", prefixes=list(["/"])) & Filters.user(userIdList) & Filters.private)
async def command3(_, message: Message):
	"""
		Command that reply with a keyboard
	"""
	keyboard = ReplyKeyboardMarkup(keyboard=list([list([KeyboardButton("Text"), ...]), ...]), resize_keyboard=True, one_time_keyboard=False)
	await message.reply_text("Text", reply_markup=keyboard)
	logger.info("I have answered to /command3 because of @{}.".format(message.from_user.username))


@app.on_message(Filters.command("help", prefixes=list(["/"])) & Filters.user(userIdList) & Filters.private)
async def help(_, message: Message):
	"""
		/help
	"""
	global commands

	await message.reply_text("In this section you will find the list of the command of the bot.\n\t{}.".format("\n\t".join(commands)))
	logger.info("I helped @{}.".format(message.from_user.username))


@app.on_inline_query(Filters.user(userIdList))
async def inline(_, inline_query: InlineQuery):
	"""
		Inline command
	"""
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


@app.on_message(Filters.command("removeadmin", prefixes=list(["/"])) & Filters.user(adminsIdList) & Filters.private)
async def removeAdmin(_, message: Message):
	"""
		/removeadmin <username>
	"""
	message.command.pop(0)
	if len(message.command) != 1:
		await message.reply_text("The syntax is: <code>/removeadmin &lt;username&gt;</code>.")
		logger.info("I helped @{} with /removeadmin.".format(message.from_user.username))
		return
	for i in range(constants.admins.shape[0]):
		if constants.admins.at[i, "username"].lower() == message.command[0].lower():
			if constants.creator == constants.admins.at[i, "id"]:
				await message.reply_text("You can\'t remove the creator of the bot from the admin list.")
				await client.send_message(constants.creator, "@{} have tried to remove you as admin.".format(message.from_user.username))
				logger.info("@{} tried to remove you as admin.".format(message.from_user.username))
				return
			else:
				message.command[0] = constants.admins.at[i, "username"]
				constants.admins.drop(list([i]))
				break
	constants.admins.reset_index(drop=True)
	constants.save()
	await message.reply_text("Admin removed.")
	logger.info("I removed @{} from the admin database.".format(message.command[0]))


@app.on_message(Filters.command("report", prefixes=list(["/"])) & Filters.user(constants.creator) & Filters.private)
async def report(_, message: Message):
	"""
		/report
	"""
	global commands

	text = list(map(lambda n: "{} - Description".format(n), commands))
	await message.reply_text("\n".join(text))
	logger.info("I send a report.")


@app.on_message(Filters.text & Filters.user(userIdList) & Filters.private)
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


@app.on_message(Filters.command("start", prefixes=list(["/"])) & Filters.user(userIdList) & Filters.private)
async def start(_, message: Message):
	"""
		/start
	"""
	await message.reply_text("Welcome @{}.\nThis bot ...".format(message.from_user.username))
	logger.info("I started because of @{}.".format(message.from_user.username))


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
async def unknown(_, message: Message):
	await message.reply_text("This command isn\'t supported.")
	logger.info("I managed an unsupported command.")


logger.info("Client initializated\nSetting the markup syntax ...")
app.set_parse_mode("html")
logger.info("Setted the markup syntax\nSetting the Job Queue ...")
logger.info("Setted the Job Queue\nStarted serving ...")
with app:
	while True:
		scheduler.run_pending()
