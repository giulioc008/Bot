# How to Contribute
For contribute at this project, open an Issue or a Pull request.

### Rules to Code

* For indentation, use Tabs instead Spaces
* For the strings delimiter, use `'`, except if the strings contain the special character `\n`
* If you want insert a variable into a string, use the concatenation (_i.e._ `$var2 = 'text' . $var1 . 'text'`)
* If possible, prefer the operator instead of the function (_i.e._ use `[]=` instead of `array_push()`)
* Every special character must be inserted like HTMl code (_i.e._ use `&apos;` instead of `'`)
* Use of `empty($var) === FALSE` instead of `isset($var)` if you want check if a variable is setted, beacuse `empty()` execute more controls

### Util links

* [How to work with branches](https://www.robinwieruch.de/git-team-workflow)
