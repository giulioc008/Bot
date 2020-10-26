# Bot template

**PHP template** for building a **Bot**

## Contents

* [File](#files)
	- [Bot.service](#bot.service)
	- [Bot.sql](#bot.sql)
	- [composer.json](#composer.json)
	- [functions.php](#functions.php)
	- [index.php](#index.php)
* [Modules](#modules)
	- [amphp/amp](#amphp/amp)
	- [amphp/http-client](#amphp/http-client)
	- [amphp/mysql](#amphp/mysql)
	- [danog/loop](#danog/loop)
	- [danog/madelineproto](#danog/madelineproto)
	- [danog/tg-file-decoder](#danog/tg-file-decoder)
* [How to install the dependencies](#how-to-install-the-dependencies)
* [Practical advice](#practical-advice)
* [ToDo list](#todo-list)

## File

Below is a description of the most important files you can find in this repo:

### Bot.service

This file is the System Unit that allow to start the bot automatically at the boot of the computer.

### Bot.sql

This file contains the bot's database source code.

### composer.json

This file contains the data about the libraries used into the bot's source code.

### functions.php

This file contains the functions that will be used into the bot's source code.

### index.php

This file contains the bot's source code.

## Modules
### amphp/amp

Module used to create the loops that automatize some function (checks of the TTL, update of the database, etc.)

* Module name: **Amp\Loop**
* Website: https://amphp.org/
* Documentation: https://amphp.org/amp/

### amphp/http-client

Module used to execute the HTTP(S) query

* Website: https://amphp.org/
* Documentation: https://amphp.org/http-client/

### amphp/mysql

Module used to create the connection to the database

* Module name: **Amp\Mysql**
* Website: https://amphp.org/
* Documentation: https://github.com/amphp/mysql#documentation--examples

### danog/loop

Module used to create the randomic loop that manage the experience

* Module name: **danog\Loop**
* Documentation: https://github.com/danog/loop#genericloop

### danog/madelineproto

Module used to create the Bot

* Module name: **\danog\MadelineProto**
* Documentation: https://docs.madelineproto.xyz/
* Requirements:
	- PHP >= 7.4

### danog/tg-file-decoder

Module used for managing the file on Telegram's server

* Documentation: https://github.com/danog/tg-file-decoder#examples

## How to install the dependencies

To install the dependencies, install [composer](https://nekobin.com/sagumirohe.bash) and use: `composer update`

## Practical advice

I recommend using an editor or IDE to work with PHP. [Atom](https://atom.io) or [PhpStorm](https://www.jetbrains.com/phpstorm/), for example, are, respectively, an advanced text editor and an IDE, both with many plugins, including git project management and direct link with GitHub: set correctly, each time it is saved it automatically loads the file on the GitHub repo. There are a thousand more or less advanced editors, you can even avoid using it and rely on the built-in GitHub one, but if you want to work locally it's better to equip yourself with something better than a simple notepad !

## ToDo list
- [ ] Complete the function with the code retrieves the entire history of a chat/channel.
- [ ] Complete the function with code that makes the user admin in all groups in common with the bot.
