# How to Contribute
For contribute at this project, open an Issue or a Pull request.

### Rules to Code

* For indentation, use Tabs instead Spaces (1 Tab = 4 Spaces)
* For the strings delimiter, use `'`
* If you want insert a newline, use the concatenation with the string `"\n"` (_i.e._ `'text' . "\n" . 'text'` instead of `'text\ntext'`) (only for PHP)
* If you want insert a variable into a string, use the concatenation (_i.e._ `$var2 = 'text' . $var1 . 'text'`) (only for PHP)
* If possible, prefer the operator instead of the function (_i.e._ use `[]=` instead of `array_push()`) (only for PHP)
* Every special character must be inserted like HTMl code (_i.e._ use `&apos;` instead of `'`)
* Use of `empty($var) === FALSE` instead of `isset($var)` if you want check if a variable is set, because `empty()` executes more controls (only for PHP)
* Use `FALSE` and `TRUE` instead of `false` and `true` (only for PHP)
* Comment all the function with extreme precision
* Use the following example to comment a function (only for PHP)
	```
		/**
		* <description_of_the_function>
		*
		* @param <type> <name_of_the_variable> <description_of_the_variable>
		* @param <type> <name_of_the_variable> <description_of_the_variable>
		* ...
		*
		* @return <type> <description_of_the_result>
		*/
	```
* For declare a variable, use the symbol of the type instead of the constructor (_i.e._ use `[]` instead of `array()` or `list()`)
* Into the functions, insert a space after the comma (_i.e._ use `func($var1, $var2)` instead of `func($var1,$var2)`, use `def func(var1, var2)` instead of `def func(var1,var2)`)
* Specify the type of the parameter of a function (_i.e._ use `func($var1, int $var2)` instead of `func($var1, $var2)`, use `def func(var1, int: var2)` instead of `def func(var1, var2)`)
* Specify the type of the return of a function (_i.e._ use `func($var1) : int` instead of `func($var1)`, use `def func(var1) -> int` instead of `def func(var1)`)
* Use the [PEP 8](https://www.python.org/dev/peps/pep-0008/) standard
* Use of keyword `global` if there is a reference to a variable outside the function

### Util links

* [How to work with branches](https://www.robinwieruch.de/git-team-workflow)
* [How to programming asynchronously](https://medium.com/@pekelny/fake-event-loop-python3-7498761af5e0)
