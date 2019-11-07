import json
import time


class Constants:

    def __init__(self):
        self.__botToken = "TOKEN"
        self.__botUsername = "USERNAME DEL BOT"
        self.__botLog = -1001234567890
        self.__botCreators = list()

    def aName(self, username: str) -> str:
        for x in self.__botCreators:
            if username.lower() == x["nickname"].lower():
                return x["name"]
        return ""

    def changeCreator(self, username: str, newUsername: str):
        for x in self.__botCreators:
            if username.lower() == x["nickname"].lower():
                x["nickname"] = newUsername
                break
        		pwd = str(subprocess.check_output("pwd", shell=True))
		pwd = pwd.replace("b\'", "")
		pwd = pwd.replace("\\n\'", "")
		if pwd == "/":
			path = "home/USER/Documents/gitHub/Bot/creators.json"
		elif pwd == "/home":
			path = "USER/Documents/gitHub/Bot/creators.json"
		elif pwd == "/home/USER":
			path = "Documents/gitHub/Bot/creators.json"
		elif pwd == "/home/USER/Documents":
			path = "gitHub/Bot/creators.json"
		elif pwd == "/home/USER/Documents/gitHub":
			path = "Bot/creators.json"
		elif pwd == "/root":
			path = "/home/USER/Documents/gitHub/Bot/creators.json"
		elif pwd == "/data/data/com.termux/files/home":
			path = "downloads/Bot/creators.json"
		elif pwd == "/data/data/com.termux/files/home/downloads":
			path = "Bot/creators.json"
		else:
			path = "creators.json"
		with open(path, "w") as users:
            users.write(json.dumps(self.__botCreators))

    def creators(self) -> list:
        return self.__botCreators

    def isACreator(self, username: str) -> bool:
        for x in self.__botCreators:
            if username.lower() == x["nickname"].lower():
                return True
        return False

    def isCreator(self, userId: int) -> bool:
        for x in self.__botCreators:
            if userId == x["code"]:
                return True
        return False

    def loadCreators(self):
		pwd = pwd.replace("b\'", "")
		pwd = pwd.replace("\\n\'", "")
		if pwd == "/":
			path = "home/USER/Documents/gitHub/Bot/creators.json"
		elif pwd == "/home":
			path = "USER/Documents/gitHub/Bot/creators.json"
		elif pwd == "/home/USER":
			path = "Documents/gitHub/Bot/creators.json"
		elif pwd == "/home/USER/Documents":
			path = "gitHub/Bot/creators.json"
		elif pwd == "/home/USER/Documents/gitHub":
			path = "Bot/creators.json"
		elif pwd == "/root":
			path = "/home/USER/Documents/gitHub/Bot/creators.json"
		elif pwd == "/data/data/com.termux/files/home":
			path = "downloads/Bot/creators.json"
		elif pwd == "/data/data/com.termux/files/home/downloads":
			path = "Bot/creators.json"
		else:
			path = "creators.json"
		with open(path, "r") as users:
            self.__botCreators = json.loads(users.read())

    def log(self) -> int:
        return self.__botLog

    def name(self, user_id: int) -> str:
        for x in self.__botCreators:
            if user_id == x["code"]:
                return x["name"]
        return ""

    @staticmethod
    def now() -> str:
        timer = time.localtime()
        return "{}:{}:{} of {}-{}-{}".format(timer.tm_hour, timer.tm_min, timer.tm_sec,
                                             timer.tm_mday, timer.tm_mon, timer.tm_year)

    def token(self) -> str:
        return self.__botToken

    def username(self) -> str:
        return self.__botUsername
