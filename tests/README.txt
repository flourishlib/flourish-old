== General Usage ==
-------------------

The unit tests provided are written using the PHPUnit testing framework
(http://www.phpunit.de/). This will need to be installed on the system you
wish to run the tests on. It is known to run on PHPUnit 3.4.9 and newer, however
it may also run on older versions.

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


== System Requirements ==
-------------------------

The unit tests are written in such a way that only the tests that can be run
on your system will be executed. Missing PHP extensions are the main cause for
tests to be skipped. Also, on some operating systems, certain tests can not be
run.

For instance, XCache does not support using the var cache on the command line
in Windows, so the test for the fCache XCache backend will never execute on
Windows. Similarly, the unixODBC drivers for linux/bsd systems does not
support some of the statements used by fDatabase to ensure UTF-8 encoding, so
any ODBC tests will only ever run on Windows.

All systems running these tests will need the bourne shell (sh) available - so
if you are running them on Windows, you will need to make sure you have cygwin
installed. In addition to 

Depending on what database extensions are installed, you will also need command
line programs for those databases to perform cleanup operations. Here is the
list of database and required command line program:

 - PostgreSQL: psql
 - MySQL: mysql
 - MSSQL: sqsh
 - Oracle: sqlplus
 - SQLite: *none*
 
In addition to these programs, you will also need a database of each type. Each
database should have a user named flourish with a password "password", and the
flourish user should be able to create and drop tables. In addition, you will
most likely need to edit the *.config.php files in the tests/database/ directory
to point to the correct server and port. Unfortunately the servers I use for
testing are not available for general public usage.


== Remote Tests ==
------------------

The remote test runner allows all of the tests to be pushed to a remote system
for testing. This is useful when trying to run the tests on different operating
systems and versions of PHP. The following programs are required locally for
running remote tests:

 - tar
 - ssh
 - scp
 - wget
 
The remote server must have the following programs:

 - psql
 - mysql
 - sqsh
 - sqlplus
 - tar
 - ssh server
 

== SQSH for Linux ==
------------------------------

Some linux distributions don't have an sqsh package, so here is some information
about how to compile it:

 - Download sqsh source from http://sourceforge.net/projects/sqsh/files/
 - Install freetds-dev (or freetds-devel) package for your distribution
 - Extract source files
 - Set the SYBASE environmental variable (usually to /usr): export SYBASE="/usr"
 - Enter the sqsh src directory
 - Run `./configure`
 - Redhat-based distros (such as Fedora, CentOS, RHEL) require adding -lct to
   SYBASE_LIBS in src/Makefile
 - Run `make install`
 
Some linux distributions with freetd 0.82 and sqsh 2.1.6 may require the
following be added to src/cmd_bcp.c

****************
#define BLK_VERSION_120 BLK_VERSION_100
#define BLK_VERSION_125 BLK_VERSION_100
#define BLK_VERSION_150 BLK_VERSION_100
****************
 
 
== SQSH for Windows ==
----------------------
 
Unfortunately I was never able to find a functioning version of sqsh for
Windows, however I was able to write a bash script that wraps the sqlcmd
program that comes with SQL Server 2005 (and 2008). While the APIs are very
similar, the script does resolve some slight differences.

Here is the content of the script:

****************
#!/bin/sh

PARAMS=""

while [ "$1" != "" ]; do
		case "$1" in
				'-U') PARAMS="${PARAMS} -U $2"; shift 2;;
				'-S') PARAMS="${PARAMS} -P `echo $2 | sed 's/:/,/'`"; shift 2;;
				'-L') shift 2;;
				'-P') PARAMS="${PARAMS} -P $2"; shift 2;;
				'-i') PARAMS="${PARAMS} -i $2"; shift 2;;
				'-C') PARAMS="${PARAMS} -Q "\""$2"\"; shift 2;;
				'-D') PARAMS="${PARAMS} -d $2"; shift 2;;
				'-b') shift;;
		esac
done

sh -c "sqlcmd $PARAMS"
****************