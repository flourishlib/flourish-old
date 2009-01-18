== General Usage ==
-------------------

The unit tests provided are written using the PHPUnit testing framework
(http://www.phpunit.de/). This will need to be installed on the system you
wish to run the tests on.

The easiest way to run the tests is to organize the files into the following
directory structure:

{some_directory}/
	- classes/ <- The Flourish classes/directories go in here
	- tests/   <- The test files, including this README go in here

Once you can the directory structure configured, open a terminal/command
prompt and cd into the unit_tests directory. Then simple execute the following:

php tests.php

The results of the unit tests will be output to your terminal. Enjoy!


== Advanced Usage ==
--------------------

It is also possible to test only a single class by passing the class name as
a parameter to tests.php like so:

php tests.php fNumber

If you are using a different directory structure and the Flourish class files
are not located in ../classes then you will need to edit the __autoload() 
function in tests/support/init.php.
