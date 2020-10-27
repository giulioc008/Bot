# Bot template

**PHP template** for building a **Bot** without use libraries.

## Contents

* [File](#files)
	- [types](#types)
	- [Bot.service](#botservice)
	- [Bot.sql](#botsql)
	- [functions.php](#functionsphp)
	- [index.php](#indexphp)
	- [logger.php](#loggerphp)
* [Documentation](#documentation)
* [Practical advice](#practical-advice)

## File

Below is a description of the most important files you can find in this repo:

### types

This directory contains all the classes that I will use into the bot's code.

This classes implement the objects of the Telegram's Bot API and provide its methods.

### Bot.service

This file is the System Unit that allow to start the bot automatically at the boot of the computer.

### Bot.sql

This file contains the bot's database source code.

### functions.php

This file contains the functions that will be used into the bot's source code.

### index.php

This file contains the bot's source code.

## Documentation

All the functions and the classes are an implementation of the Telegram's Bot API and so they are documented into the [Telegram's Bot API documentation](https://core.telegram.org/bots/api).

## Practical advice

I recommend using an editor or IDE to work with PHP. [Atom](https://atom.io) or [PhpStorm](https://www.jetbrains.com/phpstorm/), for example, are, respectively, an advanced text editor and an IDE, both with many plugins, including git project management and direct link with GitHub: set correctly, each time it is saved it automatically loads the file on the GitHub repo. There are a thousand more or less advanced editors, you can even avoid using it and rely on the built-in GitHub one, but if you want to work locally it's better to equip yourself with something better than a simple notepad !
