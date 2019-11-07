import json

import pandas
from telegram import Bot, Chat, InlineKeyboardButton, InlineKeyboardMarkup, InlineQueryResultArticle, \
    InputTextMessageContent, KeyboardButton, Message, ParseMode, ReplyKeyboardMarkup, Update
from telegram.constants import MAX_MESSAGE_LENGTH
from telegram.ext import CallbackContext, CallbackQueryHandler, CommandHandler, InlineQueryHandler, \
    MessageHandler, Updater
from telegram.ext.filters import Filters, MergedFilter

from modules import Constants

admins = None
constants = Constants.Constants()
initialLog = list()
pwd = str(subprocess.check_output("pwd", shell=True))
pwd = pwd.replace("b\'", "")
pwd = pwd.replace("\\n\'", "")
if pwd == "/":
	path = "home/giuliocoa/Documents/gitHub/Bot"
elif pwd == "/home":
	path = "giuliocoa/Documents/gitHub/Bot"
elif pwd == "/home/giuliocoa":
	path = "Documents/gitHub/Bot"
elif pwd == "/home/giuliocoa/Documents":
	path = "gitHub/Bot"
elif pwd == "/root":
	path = "/home/giuliocoa/Documents/gitHub/Bot"
elif pwd == "/data/data/com.termux/files/home":
	path = "downloads/Bot"
else:
	path = "Bot"


def addAdmin(up: Update, context: CallbackContext):
    """
        /addadmin [nickname]
    """
    global admins, constants, path

    message = up.message
    if isAdmin(message.from_user.username) is True:
        """
            Check if the argument is already an admin
        """
        if isAdmin(context.args[0]) is True:
            message.reply_markdown("The user si already an admin.")
            log(context.bot, "@" + message.from_user.username + "have sent an incorrect request at " +
                constants.now() + ".")
            return
        """
            Check if the syntax is correct
        """
        if len(context.args) != 1:
            message.reply_markdown("The syntax is: `/addadmin [nickname]`.")
            log(context.bot, "@" + message.from_user.username + "have sent an incorrect request at " +
                constants.now() + ".")
            return
        """
            Add the admin
        """
        admins = admins.append({"nickname": context.args[0]}, ignore_index=True)
        with open("{}/admins.json".format(path), "w") as element:
            element.write(admins.to_json(orient="records", index=False))
        message.reply_markdown("Admin added.")
        log(context.bot, "I added an admin at @" + message.from_user.username + "\'s request at " +
            constants.now() + ".")
    else:
        messageNotAllowed(context.bot, message, "Add admin")


def answerInlineButton(up: Update, context: CallbackContext):
    global constants

    message = up.message
    if isAdmin(message.from_user.username) is True:
        log(context.bot,
            "@" + up.callback_query.from_user.username + " has pressed an Inline button at " + constants.now() + ".")
        keyboard = list()
        text = ""
        if up.callback_query.data.lower() == "text":
            keyboard.append([InlineKeyboardButton("Text", callback_data="Text"), ...])
            text = "Text"
        elif up.callback_query.data.lower() == "text":
            keyboard.append([InlineKeyboardButton("Text", callback_data="Text"), ...])
            text = "Text"
        else:
            keyboard.append([InlineKeyboardButton("Text", callback_data="Text"), ...])
            text = "Text"
        up.callback_query.edit_message_text(text, parse_mode=ParseMode.MARKDOWN)
        up.callback_query.edit_message_reply_markup(InlineKeyboardMarkup(keyboard))
        log(context.bot, "I have answered to an Inline button at " + constants.now() + ".")
    else:
        messageNotAllowed(context.bot, message, "Inline button")


def automaticRemovalStatus(up: Update, context: CallbackContext):
    """
        Removing the status message
    """
    message = up.message
    message.delete()
    log(context.bot, "I removed a status message from the " + message.chat.title + " at " + constants.now() + ".")


def command1(up: Update, context: CallbackContext):
    global constants

    message = up.message
    if isAdmin(message.from_user.username) is True:
        log(context.bot, "I launched /command1 at " + constants.now() + " because of @" +
            message.from_user.username + ".")
        """
            If the command has any arguments, it can be acceded at context.args parameter
        """
    else:
        messageNotAllowed(context.bot, message, "Command #1")


def command2(up: Update, context: CallbackContext):
    """
        /command2
    """
    global constants

    message = up.message
    if isAdmin(message.from_user.username) is True:
        log(context.bot,
            "@" + message.from_user.username + " have launched /command2 command at " + constants.now() + ".")
        """
            Checking if the Job Queue that Text is already active
        """
        if len(context.job_queue.get_jobs_by_name("Text")) == 0:
            job = context.job_queue.run_repeating(queue1, XXXXXXXXX)
            job.name = "Text"
            message.reply_markdown("Text")
            if constants.isCreator(message.from_user.id) is True:
                log(context.bot,
                    constants.name(
                        message.from_user.id) + " activated the Job Queue that Text " +
                    constants.now() + ".\nIt, every XXXXXXXXX seconds, Text.")
            else:
                log(context.bot,
                    "@" + message.from_user.username + " activated the Job Queue that Text " +
                    constants.now() + ".\nIt, every XXXXXXXXX seconds, Text.")
        else:
            message.reply_markdown("The Job Queue that Text is already activated.")
            if constants.isCreator(message.from_user.id) is True:
                log(context.bot,
                    constants.name(
                        message.from_user.id) + " tried to start the Job Queue that Text at " +
                    constants.now() + ", although it was already active")
            else:
                log(context.bot,
                    "@" + message.from_user.username + " tried to start the Job Queue that Text " +
                    "at " + constants.now() + ", although it was already active")
        if len(context.job_queue.get_jobs_by_name("Text")) == 0:
            job = context.job_queue.run_repeating(queue2, XXXXXXXXX)
            job.name = "Text"
            message.reply_markdown("Text")
            if constants.isCreator(message.from_user.id) is True:
                log(context.bot,
                    constants.name(
                        message.from_user.id) + " activated the Job Queue that Text " +
                    constants.now() + ".\nIt, every XXXXXXXXX seconds, Text.")
            else:
                log(context.bot,
                    "@" + message.from_user.username + " activated the Job Queue that Text " +
                    constants.now() + ".\nIt, every XXXXXXXXX seconds, Text.")
        else:
            message.reply_markdown("The Job Queue that Text is already activated.")
            if constants.isCreator(message.from_user.id) is True:
                log(context.bot,
                    constants.name(
                        message.from_user.id) + " tried to start the Job Queue that Text at " +
                    constants.now() + ", although it was already active")
            else:
                log(context.bot,
                    "@" + message.from_user.username + " tried to start the Job Queue that Text " +
                    "at " + constants.now() + ", although it was already active")

        ...

        context.job_queue.start()
    else:
        messageNotAllowed(context.bot, message, "Command #2")


def command3(up: Update, context: CallbackContext):
    """
        Command that reply with a keyboard
    """
    global constants

    message = up.message
    if isAdmin(message.from_user.username) is True:
        log(context.bot, "I launched /command3 at " + constants.now() + " because of @" +
            message.from_user.username + ".")
        keyboard = ReplyKeyboardMarkup([KeyboardButton("Text"), ...])
        message.reply_markdown("Text", reply_markup=keyboard, resize_keyboard=True)
    else:
        messageNotAllowed(context.bot, message, "Command #3")


def error(up: Update, context: CallbackContext):
    log(context.bot, "Update {} caused error {}".format(up, context.error))


def help(up: Update, context: CallbackContext):
    """
        /help
    """
    global constants

    message = up.message
    if isAdmin(message.from_user.username) is True:
        message.reply_markdown("In this section you will find all the help you need to use the bot.\n"
                               "/command1 -> command1\n" +

                                ...

                               "/help -> Show the help.")
        log(context.bot, "I helped @" + message.from_user.username + " at " + constants.now() + ".")
    else:
        messageNotAllowed(context.bot, message, "Help")


def inline(up: Update, context: CallbackContext):
    """
        Inline command
    """
    global constants

    message = up.message
    inlineQuery = up.inline_query
    if isAdmin(inlineQuery.from_user.username) is True:
        results = list()
        queryId = ""
        title = ""
        text = ""
        """
            Checking if the text of the query is correct
        """
        if inlineQuery.query.lower() == "text":
            queryId = "Text"
            title = "Text"
            text = InputTextMessageContent("Text", parse_mode=ParseMode.MARKDOWN, disable_web_page_preview=True)
        elif inlineQuery.query.lower() == "text":
            queryId = "Text"
            title = "Text"
            text = InputTextMessageContent("Text", parse_mode=ParseMode.MARKDOWN, disable_web_page_preview=True)

            ...

        else:
            queryId = "Text"
            title = "Text"
            text = InputTextMessageContent("Text", parse_mode=ParseMode.MARKDOWN, disable_web_page_preview=True)
        results.append(InlineQueryResultArticle(id=queryId, title=title, input_message_content=text))
        """
            Sending the output
        """
        inlineQuery.answer(results)
        log(context.bot, "I sent the answer to the Inline Query of @" + inlineQuery.from_user.username + ".")
    else:
        messageNotAllowed(context.bot,
                          Message(0, inlineQuery.from_user, constants.now(), Chat(inlineQuery.from_user.id, "")),
                          "Inline mode")


def isAdmin(username: str) -> bool:
    global admins

    rows = admins.shape[0]
    for element in range(rows):
        if admins.at[element, "nickname"].lower() == username.lower():
            return True
    return False


def log(bot: Bot = None, logging: str = ""):
    global constants, initialLog

    if bot is not None:
        if initialLog is not None:
            for message in initialLog:
                for k in range(0, len(message), MAX_MESSAGE_LENGTH):
                    bot.sendMessage(chat_id=constants.log(),
                                    text=message[k * MAX_MESSAGE_LENGTH:(k + 1) * MAX_MESSAGE_LENGTH],
                                    parse_mode=ParseMode.MARKDOWN)
            initialLog = None
        for k in range(0, len(logging), MAX_MESSAGE_LENGTH):
            bot.sendMessage(chat_id=constants.log(),
                            text=logging[k * MAX_MESSAGE_LENGTH:(k + 1) * MAX_MESSAGE_LENGTH],
                            parse_mode=ParseMode.MARKDOWN)
    else:
        initialLog.append(logging)


def messageNotAllowed(bot: Bot, msg: Message, request: str):
    msg.reply_markdown("You aren\'t an allowed user.")
    log(bot, request + " requested by an unauthorized user (@" + msg.from_user.username + ") at " + constants.now() +
        ".")


def queue1(context: CallbackContext):
    """
        Do a Job in the Job Queue
    """
    job = context.job


def queue2(context: CallbackContext):
    """
        Do a Job in the Job Queue
    """
    job = context.job


def removeAdmin(up: Update, context: CallbackContext):
    """
        /removeadmin [nickname]
    """
    global admins, constants, path

    message = up.message
    if isAdmin(message.from_user.username) is True:
        if len(context.args) != 1:
            message.reply_markdown("The syntax is: `/removeadmin [nickname]`.")
            log(context.bot,
                "I helped @" + message.from_user.username + " with /removeadmin at " + constants.now() + ".")
            return
        if constants.isACreator(context.args[0]):
            message.reply_markdown("You can\'t remove the creator of the bot from the admin list.")
            log(context.bot, "@" + message.from_user.username + " tried to remove you as admin at " + constants.now() +
                ".")
            return
        rows = admins.shape[0]
        for i in range(rows):
            if admins.at[i, "nickname"].lower() == context.args[0].lower():
                context.args[0] = admins.at[i, "nickname"]
                admins.drop([i])
                break
        admins.reset_index(drop=True)
        with open("{}/admins.json".format(path), "w") as element:
            element.write(admins.to_json(orient="records", index=False))
        message.reply_markdown("Admin removed.")
        log(context.bot, "I removed an admin (@" + context.args[0] + ") at @" + message.from_user.username +
            "\'s request at " + constants.now() + ".")
    else:
        messageNotAllowed(context.bot, message, "Remove admin")


def report(up: Update, context: CallbackContext):
    """
        /report
    """
    global constants

    message = up.message
    if isAdmin(message.from_user.username) is True:
        if constants.isCreator(message.from_user.id) is True:
            message.reply_markdown("command1 - command1\n" +

                                    ...

                                   "help - Show the help.")
            log(context.bot, "I send a report to @" + message.from_user.username + " at " + constants.now() + ".")
    else:
        messageNotAllowed(context.bot, message, "Report")


def split(up: Update, context: CallbackContext):
    message = up.message
    if message.text is not None:
        if message.text == " ... ":
            pass
        elif message.text == " ... ":
            pass

            ...

        else:
            pass


def start(up: Update, context: CallbackContext):
    """
        /start
    """
    global constants

    message = up.message
    if isAdmin(message.from_user.username) is True:
        message.reply_markdown("Welcome @" + message.from_user.username + ".\nThis ...")
        log(context.bot, "I started at " + constants.now() + " because of @" + message.from_user.username + ".")
    else:
        messageNotAllowed(context.bot, message, "Start")


def unknown(up: Update, context: CallbackContext):
    global constants

    message = up.message
    if isAdmin(message.from_user.username) is True:
        message.reply_markdown("The insert command is wrong.\nInsert a new command.")
        log(context.bot, "Unknown command at " + constants.now() + " because of @" + message.from_user.username + ".")
    else:
        messageNotAllowed(context.bot, message, "Unknown")


if __name__ == "__main__":
    log(logging="Initializing the Admins ...")
    constants.loadCreators()
    with open("{}/admins.json".format(path), "r") as users:
        admins = pandas.DataFrame(data=json.load(users), columns=["nickname", "id"])
    log(logging="Admins initializated\nInitializing the Updater ...")
    updater = Updater(token=constants.token(), use_context=True)
    log(logging="Updater initializated\nDispatching the handlers ...")
    dispatcher = updater.dispatcher
    # /addadmin
    dispatcher.add_handler(CommandHandler("addadmin", addAdmin, filters=Filters.private))
    # /command1
    dispatcher.add_handler(CommandHandler("command1", command1, filters=Filters.private))
    # /command2
    dispatcher.add_handler(CommandHandler("command2", command2, filters=Filters.private))
    # /command3
    dispatcher.add_handler(CommandHandler("command3", command3, filters=Filters.private))
    # /help
    dispatcher.add_handler(CommandHandler("help", help, filters=Filters.private))
    # /removeadmin
    dispatcher.add_handler(CommandHandler("removeadmin", removeAdmin, filters=Filters.private))
    # /report
    dispatcher.add_handler(CommandHandler("report", report, filters=Filters.private))
    # /start
    dispatcher.add_handler(CommandHandler("start", start, filters=Filters.private))
    # Generic text
    dispatcher.add_handler(MessageHandler(MergedFilter(Filters.private, and_filter=Filters.text), split))
    # Unknown command
    dispatcher.add_handler(MessageHandler(MergedFilter(Filters.private, and_filter=Filters.command), unknown))
    # Inline Mode
    dispatcher.add_handler(InlineQueryHandler(inline))
    # Inline button
    dispatcher.add_handler(CallbackQueryHandler(answerInlineButton))
    # Error handler
    dispatcher.add_error_handler(error)
    # Automatic removal of status messages
    dispatcher.add_handler(
        MessageHandler(MergedFilter(Filters.chat(chat_id=XXXXXXXXXXXXX), and_filter=Filters.status_update),
                       automaticRemovalStatus))
    log(logging="Handlers dispatched\nStart polling ...")
    updater.start_polling()
    """
        log(logging="Handlers dispatched\nStart Webhook ...")
        updater.start_webhook(listen="0.0.0.0", port=int(os.environ.get("PORT", "8443")), url_path=constants.token())
        updater.bot.setWebhook("https://{0}.herokuapp.com/{1}".format(constants.username().lower(), constants.token()))
    """
    log(logging="Started serving @" + constants.username() + " ...")
    updater.idle()
