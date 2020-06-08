# Bot template

**Python template** for building a **Bot**



## Modules

### Aiofile

Modules used to do asynchronous file operations

* Version: 1.5.2
* Documentation: https://github.com/mosquito/aiofile
* Modules name: **aiofile**
* Installing: `pip install --upgrade --no-cache-dir aiofile`



### APScheduler

Library that lets you schedule your Python code to be executed later, either just once or periodically

* Version: 3.6.3
* Website: https://apscheduler.readthedocs.io/en/stable/index.html
* Documentation: https://apscheduler.readthedocs.io/en/stable/modules/schedulers/asyncio.html#module-apscheduler.schedulers.asyncio
* Modules name: **apscheduler**
* Requirements:
	- Python >= 3.4: none
	- Python 3.3: **asyncio**
	- Python <= 3.2: **trollius**
* Installing: `pip install --upgrade --no-cache-dir APScheduler`



### PyMySQL

Module used to connect to a MySQL Server

* Version: 0.9.3
* Website: https://pymysql.readthedocs.io/en/latest/
* Documentation: https://pymysql.readthedocs.io/en/latest/modules/index.html
* Module name: **pymysql**
* Requirements:
	- Python -- one of the following:
		+ [CPython](http://www.python.org/) : 2.7 and >= 3.5
		+ [PyPy](http://pypy.org/) : Latest version
	- MySQL Server -- one of the following:
		+ [MySQL](http://www.mysql.com/) >= 5.5
		+ [MariaDB](https://mariadb.org/) >= 5.5
* Installing: `pip install --upgrade --no-cache-dir PyMySQL`



### Pyrogram

Module used to create the UserBot

* Version: 0.17.0-async
* Website: https://docs.pyrogram.org/
* Documentation: https://docs.pyrogram.org/api/client
* Module name: **pyrogram**
* Requirements: **TgCrypto**
* Installing: `pip install --upgrade --no-cache-dir https://github.com/pyrogram/pyrogram/archive/asyncio.zip; pip install --upgrade --no-cache-dir TgCrypto`



### Requests

HTTP library for Python, built for human beings

* Version: 2.23.0
* Website: https://requests.readthedocs.io/en/master/
* Documentation: https://requests.readthedocs.io/en/master/user/advanced/
* Module name: **requests**
* Installing: `pip install --upgrade --no-cache-dir requests`



### Telegraph

Python Telegraph API wrapper

* Version: 1.4.0
* Website: https://python-telegraph.readthedocs.io/en/latest/
* Documentation: https://python-telegraph.readthedocs.io/en/latest/telegraph.html
* Module name: **telegraph**
* Requirements: **requests**
* Installing: `pip install --upgrade --no-cache-dir telegraph`



## How to install the dependencies

To install the dependencies, create a [Virtual Enviroment](https://packaging.python.org/tutorials/installing-packages/#creating-virtual-environments) and use: `pip install -r requirements.txt`
