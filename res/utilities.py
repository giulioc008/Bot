import asyncio
from pymysql.connections import Connection
from pyrogram import Client, Filters, InlineKeyboardButton, Message
from pyrogram.errors import FloodWait
from res.configurations import Configurations


async def chat_button(client: Client, chat: dict, connection: Connection) -> InlineKeyboardButton:
	"""
		A coroutine that creates an InlineKeyboardButton form tha data of a chat
		:param client: The application
		:param chat: The chat's data
		:return: InlineKeyboardButton
	"""
	if chat["username"] is not None:
		invite_link = "https://t.me/{}".format(chat["username"])
	elif chat["invite_link"] is not None:
		invite_link = chat["invite_link"]
	else:
		# Generating the new invite_link
		invite_link = await client.export_chat_invite_link(int(chat["id"]))

		# Saving the new invite_link
		with connection.cursor() as cursor:
			cursor.execute("UPDATE `Chats` SET `invite_link`=%(invite_link)s WHERE `id`=%(id)s;", {
				"id": int(chat["id"]),
				"invite_link": invite_link
			})
		connection.commit()

	return InlineKeyboardButton(text=chat["title"], url=invite_link)


async def split_edit_text(config: Configurations, message: Message, text: str, **options):
	"""
		A coroutine that edits the text of a message; if text is too long sends more messages.
		:param message: Message to edit
		:param text: Text to insert
		:return: None
	"""
	await message.edit_text(text[: config.get("message_max_length")], options)
	if len(text) >= config.get("message_max_length"):
		for i in range(1, len(text), config.get("message_max_length")):
			try:
				await message.reply_text(text[i : i + config.get("message_max_length")], options, quote=True)
			except FloodWait as e:
				await asyncio.sleep(e.x)


async def split_reply_text(config: Configurations, message: Message, text: str, **options):
	"""
		A coroutine that reply to a message; if text is too long sends more messages.
		:param message: Message to reply
		:param text: Text to insert
		:return: None
	"""
	await message.reply_text(text[: config.get("message_max_length")], options)
	if len(text) >= config.get("message_max_length"):
		for i in range(1, len(text), config.get("message_max_length")):
			try:
				await message.reply_text(text[i : i + config.get("message_max_length")], options)
			except FloodWait as e:
				await asyncio.sleep(e.x)


def unknown_filter(config: Configurations):
	def func(flt, message: Message):
		text = message.text
		if text:
			message.matches = list(flt.p.finditer(text)) or None
			if message.matches is False and text.startswith("/") is True and len(text) > 1:
				return True
		return False

	commands = list(map(lambda n: n["name"], config.get("commands")))

	return Filters.create(func, "UnknownFilter", p=re.compile("/{}".format("|/".join(commands)), 0))
