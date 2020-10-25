# Bot template

**PHP template** for building a **Bot**

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

## ToDo list
- [ ] Complete the function with the code retrieves the entire history of a chat/channel.
- [ ] Complete the function with code that makes the user admin in all groups in common with the bot.
