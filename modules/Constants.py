import json
import time


class Constants:

    def __init__(self):
        self.__botToken = "TOKEN"
        self.__botUsername = "Bot"
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
        with open("creators.json", "w") as users:
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
        with open("creators.json", "r") as users:
            self.__botCreators = json.load(users)

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
        return "{0}:{1}:{2} of {3}-{4}-{5}".format(timer.tm_hour, timer.tm_min, timer.tm_sec,
                                                   timer.tm_mday, timer.tm_mon, timer.tm_year)

    def token(self) -> str:
        return self.__botToken

    def username(self) -> str:
        return self.__botUsername
