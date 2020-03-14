import json
import subprocess

import pandas


class Constants:

	def __init__(self):
		self.__appHash = "HASH"
		self.__appId = 0
		self.__botAdmins = None
		self.__botUsername = "Bot"
		self.__botToken = "TOKEN DEL BOT"
		self.__users = None
		self.__creator = 0
		pwd = str(subprocess.check_output("pwd", shell=True))
		pwd = pwd.replace("b\'", "")
		pwd = pwd.replace("\\n\'", "")
		if pwd == "/":
			self.__path = "home/USER/Documents/gitHub/{}/".format(self.__botUsername)
		elif pwd == "/home":
			self.__path = "USER/Documents/gitHub/{}/".format(self.__botUsername)
		elif pwd == "/home/USER":
			self.__path = "Documents/gitHub/{}/".format(self.__botUsername)
		elif pwd == "/home/USER/Documents":
			self.__path = "gitHub/{}/".format(self.__botUsername)
		elif pwd == "/home/USER/Documents/gitHub":
			self.__path = "{}/".format(self.__botUsername)
		elif pwd == "/root":
			self.__path = "/home/USER/Documents/gitHub/{}/".format(self.__botUsername)
		elif pwd == "/data/data/com.termux/files/home":
			self.__path = "downloads/{}/".format(self.__botUsername)
		elif pwd == "/data/data/com.termux/files/home/downloads":
			self.__path = "{}/".format(self.__botUsername)
		else:
			self.__path = "./"

	@property
	def admins(self) -> pandas.DataFrame:
		return self.__botAdmins

	@admins.setter
	def admins(self, newAdmin: list):
		self.__botAdmins = self.__botAdmins.append(newAdmin, ignore_index=True)
		element = "{\"admins\":" + self.__botAdmins.to_json(orient="records").replace("\":", "\": ").replace(",\"", ", \"") + ",\"users\":" + \
				  self.__users.to_json(orient="records").replace("\":", "\": ").replace(",\"", ", \"") + "}"
		"""
			Saving the database
		"""
		with open("{}database.json".format(self.__path), "w") as users:
			users.write(element)

	@admins.deleter
	def admins(self):
		self.__botAdmins = pandas.DataFrame(data=dict(), columns=list(["id", "is_self", "is_contact",
																	   "is_mutual_contact", "is_deleted",
																	   "is_bot", "is_verified", "is_restricted",
																	   "is_scam", "is_support", "first_name",
																	   "last_name", "username", "language_code",
																	   "phone_number"]))
		element = "{\"admins\": [],\"users\":" + self.__users.to_json(orient="records").replace("\":", "\": ").replace(",\"", ", \"") + "}"
		"""
			Saving the database
		"""
		with open("{}database.json".format(self.__path), "w") as users:
			users.write(element)

	@property
	def creator(self) -> int:
		return self.__creator

	@property
	def databasePath(self) -> str:
		return self.__path

	@property
	def hash(self) -> str:
		return self.__appHash

	@property
	def id(self) -> int:
		return self.__appId

	def loadCreators(self):
		"""
			Reading the database
		"""
		with open("{}database.json".format(self.__path), "r") as users:
			files = json.load(users)
			"""
		Setting the database
		"""
			self.__botAdmins = pandas.DataFrame(data=files["admins"], columns=list(["id", "is_self", "is_contact",
																					"is_mutual_contact", "is_deleted",
																					"is_bot", "is_verified", "is_restricted",
																					"is_scam", "is_support", "first_name",
																					"last_name", "username", "language_code",
																					"phone_number"]))
			self.__users = pandas.DataFrame(data=files["users"], columns=list(["id", "is_self", "is_contact",
																			   "is_mutual_contact", "is_deleted",
																			   "is_bot", "is_verified", "is_restricted",
																			   "is_scam", "is_support", "first_name",
																			   "last_name", "username", "language_code",
																			   "phone_number", "flag"]))
		"""
			Setting the parameters
		"""
		for i in range(self.__botAdmins.shape[0]):
			if self.__botAdmins.at[i, "username"] == "USERNAME":
				self.__creator = int(self.__botAdmins.at[i, "id"])

	def save(self):
		element = "{\"admins\":" + self.__botAdmins.to_json(orient="records").replace("\":", "\": ").replace(",\"", ", \"") + ",\"users\":" + \
				  self.__users.to_json(orient="records").replace("\":", "\": ").replace(",\"", ", \"") + "}"
		"""
			Saving the database
		"""
		with open("{}database.json".format(self.__path), "w") as users:
			users.write(element)

	@property
	def token(self) -> str:
		return self.__botToken

	@property
	def username(self) -> str:
		return self.__botUsername

	@property
	def users(self) -> pandas.DataFrame:
		return self.__users

	@users.setter
	def users(self, newUser: list):
		self.__users = self.__chat.append(newUser, ignore_index=True)
		element = "{\"admins\":" + self.__botAdmins.to_json(orient="records").replace("\":", "\": ").replace(",\"", ", \"") + ",\"users\":" + \
				  self.__users.to_json(orient="records").replace("\":", "\": ").replace(",\"", ", \"") + "}"
		"""
			Saving the database
		"""
		with open("{}database.json".format(self.__path), "w") as users:
			users.write(element)

	@users.deleter
	def users(self):
		self.__users = pandas.DataFrame(data=dict(), columns=list(["id", "is_self", "is_contact",
																   "is_mutual_contact", "is_deleted",
																   "is_bot", "is_verified", "is_restricted",
																   "is_scam", "is_support", "first_name",
																   "last_name", "username", "language_code",
																   "phone_number", "flag"]))
		element = "{\"admins\":" + self.__botAdmins.to_json(orient="records").replace("\":", "\": ").replace(",\"", ", \"") + ",\"users\": []}"
		"""
			Saving the database
		"""
		with open("{}database.json".format(self.__path), "w") as users:
			users.write(element)
