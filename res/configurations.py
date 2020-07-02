#   The goal of this object is to have a very comfortable configuration entity, which stores all configurations,
#   parsed from a file (set by a call to <code>set_file()</code> method).
#   Once created, a configuration object could be passed to every function of the bot.

import json
from aiofile import AIOFile

# This map (dict) contains the configurations for your configurations.
# class property name -> configuration name
# Only the values mapped in this dict will be imported from file, so update it every time a change is made.
DEFAULT_MAP = {
	"app_hash": "appHash",
	"app_id": "appId",
	"bot_username": "botUsername",
	"bot_token": "botToken",
	"path": "path"
}


class Configurations:
	def __init__(self, path: str, map = DEFAULT_MAP):
		self.__file_path = path
		self.__map = map

		# Setting the properties of the class
		for key in self.__map.keys():
			setattr(self, key, None)

	# Get attribute
	def get(self, name: str):
		return getattr(self, name)

	# Set attribute
	def set(self, name: str, value):
		return setattr(self, name, value)

	async def parse(self):
		# Checking if the path is set
		if self.__file_path is None:
			raise FileNotSetException()

		# Reading the file
		async with AIOFile(self.__file_path, "r") as f:
			content = await f.read()
			content = json.loads(content)

			# Setting the properties of the class
			for key in self.__map.keys():
				value = content[self.__map[key]] if self.__map[key] in content else None
				setattr(self, key, value)

	def set_map(self, new_map):
		if type(new_map).__name__ != "dict":
			raise MapNotValidException()

		self.__map = new_map


class FileNotSetException(Exception):
	# Raised when <code>parse()</code> is called before <code>set_file()</code>
	def __init__(self):
		self.__message = "Trying to parse, but no file set!"


class MapNotValidException(Exception):
	# Raised when in <code>set_map(map)</code>, <code>map</code> is not of `dict` type
	def __init__(self):
		self.__message = "Map type is not dict!"

