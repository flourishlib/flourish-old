#!/bin/bash

set -m

# This allows for the debug, quiet and info flags
# -q (QUIET) prevents showing the progress indicator - this is automatically selected
#      if the output is in JSON (done by passing .json to the test runner)
# -d (DEBUG_MODE) drops the user into the remote shell after the tests complete - this
#      is not allowed if the output is in JSON (done by passing .json to the test runner)
# -i (SHOW_INFO) displays info about the remote server including PHP/PHPUnit
#      version and info about missing PHP extensions or database command line tools - this
#      mode will not run any tests
# -a (ADD_KEY) this adds the current user's SSH public key to all of the remote accounts
#      listed - no tests will be run or info shown with this flag
# -n 0-9 (CONCURRENT) this number of tests will be run conccurently instead of sequentially
# -p (PARALLEL_DB) if this flag is present, all tests for each type of database will be
#      run sequentially on all specified hosts, but all database types will be run at the
#      same time and all non-database tests will be run in parallel with all of the database tests

QUIET=0
DEBUG_MODE=0
SHOW_INFO=0
ADD_KEY=0
SUB_CALL=0
JSON=0
TEXT=0
CONCURRENT=1
NO_MYSQL=0
NO_POSTGRESQL=0
NO_MSSQL=0
NO_ORACLE=0
NO_DB2=0
PARALLEL_DB=0

while [[ ${1:0:1} = - ]]; do
	if [[ $1 = -q ]]; then
		QUIET=1
		shift
		
	elif [[ $1 = -d ]]; then
		DEBUG_MODE=1
		shift
		
	elif [[ $1 = -i ]]; then
		SHOW_INFO=1
		shift
		
	elif [[ $1 = -a ]]; then
		ADD_KEY=1
		shift
		
	elif [[ $1 = -s ]]; then
		SUB_CALL=1
		shift
	
	elif [[ $1 = -n ]]; then
		CONCURRENT="$2"
		shift 2
	
	elif [[ $1 = -p ]]; then
		PARALLEL_DB=1
		JSON=1
		QUIET=1
		shift
	fi
done

# JSON output always triggers quiet mode since JSON is intended for programatic use
for ARG in "$@"; do
	if [[ $ARG = .json ]]; then
		JSON=1
		QUIET=1
	elif [[ $ARG = .text ]]; then
		TEXT=1
		QUIET=1
	elif [[ $ART = '!mysql*' ]]; then
		NO_MYSQL=1
		PARALLEL_DB=0
	elif [[ $ART = '!postgresql*' ]]; then
		NO_POSTGRESQL=1
		PARALLEL_DB=0
	elif [[ $ART = '!mssql*' ]]; then
		NO_MSSQL=1
		PARALLEL_DB=0
	elif [[ $ART = '!oracle*' ]]; then
		NO_ORACLE=1
		PARALLEL_DB=0
	elif [[ $ART = '!db2*' ]]; then
		NO_DB2=1
		PARALLEL_DB=0
	fi
done

# The C shell requires multi-line text to have a \ before each newline, so we have
# to detect it and change out parameters accordingly - this is true even though we
# use bash to run the tests, because bash is invoked by the default user's shell
IS_CSH=""

# Makes a queue of commands to run remotely
#
# All parameters passed to this function are added to a list of commands to be
# run remotely over SSH. Obviously any redirection commands will need to be escaped
# to prevent them being applied to this command.
queue_command()
{
	SEPARATOR=' ; '
	if [[ $1 = ";" || $1 = "&&" || $1 = "||" ]]; then
		SEPARATOR=" $1 "
		shift
	fi
	
	if [[ $COMMAND_QUEUE ]]; then
		COMMAND_QUEUE="${COMMAND_QUEUE}${SEPARATOR}"
	fi
	
	# This detects all parameters with one or more character plus blank parameters
	while [[ -n $1 || ${1-XXX} != XXX ]]; do
		ARG="$1"
		shift
		
		# quotes the value in a foo=bar or --foo=bar parameter
		if [[ $ARG =~ ^[a-zA-Z0-9_-]+= ]]; then
			VALUE=$(printf "%s" "$ARG" | sed 's/^[a-zA-Z0-9_]*=//')
			VALUE=\"$(printf "%s" "$VALUE" | sed 's/"/"\\""/g')\"
			ARG=$(printf "%s" "$ARG" | sed "s/^\([a-zA-Z0-9_]*\)=.*$/\1=$VALUE/")
			
		# lets params that are all non-alpha through, such as |, &&, >, etc
		elif [[ $ARG =~ ^[^' 'a-zA-Z]+$ ]]; then
			ARG="$ARG"
		
		# quotes any param that would be interpreted as multiple words or blank parameters
		elif [[ $ARG =~ [^a-zA-Z0-9_.\/=:@~-] || ! $ARG ]]; then
			ARG=\"$(printf "%s" "$ARG" | sed 's/"/"\\""/g' | sed 's/*/"*"/g')\"
			if [[ $IS_CSH != "" ]]; then
				ARG=${ARG/!/\\!}
				ARG=$(printf "%s\n" "$ARG" | awk 'BEGIN { RS="" } {gsub("\n", "\\\\&");print}')
			fi
		fi
		
		COMMAND_QUEUE="$COMMAND_QUEUE $ARG"
	done
}

# Executes the remote command queue on the user@host specified as the first param
#
# Any string passed as the second param will be used as the variable name to store the output
# of the command in, instead of it being done interactively
exec_commands()
{
	PORT=""
	if [[ $1 =~ : ]]; then
		PORT="-p ${1##*:}"
		SSH_HOST=${1%%:*}
	else
		SSH_HOST="$1"
	fi
	
	COMMAND_QUEUE=$(printf "%s" "$COMMAND_QUEUE" | sed "s/'/'\\\\''/g")

	if [[ $2 ]]; then
		OUTPUT=$(ssh -A -q $PORT $SSH_HOST "bash -l -c '$COMMAND_QUEUE'")
		eval "$2='$OUTPUT'"
		
	else
		ssh -A -tq $PORT $SSH_HOST "bash -l -c '$COMMAND_QUEUE'"	
	fi
	
	COMMAND_QUEUE=""
}

# This function shows a color coded status of required and optional remote software
# @param $1  The name of the software to display
# @param $2  The status of the remote software - 1 = installed, -1 = required but missing, 0 = optional but missing
# @param $3  Optional: the text to display in the colored output - overrides the default yes or no
show_status()
{
	WIDTH=${4-14}
	
	echo -n "    "
	echo "$1" | awk "{ printf "\""%-${WIDTH}s"\"", \$0 }"
	
	if [[ $2 = 1 ]]; then
		TEXT=yes
		if [[ $3 != "" ]]; then
			TEXT="$3"
		fi
		echo -e "\033[1;37;43m$TEXT\033[0m"
		
	elif [[ $2 = -1 ]]; then
		echo -e "\033[1;37;41mno\033[0m"
		
	else
		TEXT=no
		if [[ $3 != "" ]]; then
			TEXT="$TEXT $3"
		fi
		echo -e "\033[1;37;40m$TEXT\033[0m"
	fi
}

# A shortcut to show_status() that defaults to the program being required
# @param $1  The name of the software to display
# @param $2  If the software was found - 0 means no, anything else means yes
# @param $3  Optional: text to display in the colored output
show_required()
{
	STATUS=-1
	if (( $2 )); then
		STATUS=1
	fi
	show_status "$1" $STATUS $3	
}

# A shortcut to show info if either the command line interface or PHP extension for
# a database is missing. If both are present, or missing, nothing will be shown.
# @param $1  The database type
# @param $2  If a PHP extension for the database is installed - 0 for no, anything else for yes
# @param $3  If the cli for the database is installed - 0 for no, anything else for yes
show_db()
{
	STATUS=0
	TEXT=""
	
	if (( $2 && $3 )); then
		return
		
	elif (( ! $2 && $3 )); then
		TEXT=ext
		
	elif (( $2 && ! $3 )); then
		TEXT=cli
		
	else
		return
	fi
	
	show_status "$1" $STATUS $TEXT	
}

# Shows a comma-separated list of PHP extensions that are required or optional for Flourish
# and that are not installed. Optional ones are shown in grey text, required are shown with
# a red background and white text
# @param $1  The extension name
# @param $2  If the extension was found - 0 implies no, anything else implies yes
# @param $3  If the extension is required
show_ext()
{
	STATUS=0
	if (( $2 )); then
		return
	elif (( $3 )); then
		STATUS=-1
	fi
	
	if (( $EXT_NUM )); then
		echo -n ' '
	else
		echo -n '    missing exts' | awk '{ printf "%-18s", $0 }'
	fi
	
	if [[ $STATUS = -1 ]]; then
		echo -e -n "\033[0;37;41m$1\033[0m"
	else
		echo -e -n "\033[1;37;40m$1\033[0m"
	fi
	
	((EXT_NUM++))
}

# Checks for a grep regex pattern inside of a larger block of text - prints the number of
# lines that contain the search terms
# @param $1  The haystack
# @param $2  The grep regex to search for
present()
{
	printf "%s" "$1" | grep "$2" | wc -l	
}

# Takes a large block of text, splits it on separator and assigns the results to multiple
# variables
# @param $1      The text to split
# @param $2      The separator to split on
# @param $3      The name of the variable to store the first part in
# @param $4,...  The name of the variable to store the second part in
assign()
{
	VAR="$1"
	NUM=3
	while [[ $VAR ]]; do
		eval "${!NUM}=\${VAR%%?$2*}"
		OLD_VAR="$VAR"
		eval "VAR=\${VAR#*$2?}"
		if [[ $VAR = $OLD_VAR ]]; then
			VAR=""
		fi
		((NUM++))
	done
}

ACTIVITY_PID=""
# Starts an activity indicator as a background process that prints a . every 1/3 seconds
# @param $1      Text to display before the .s start
# @param $2,...  More text to display
start_activity()
{
	if (( $QUIET )); then
		return
	fi
	
	while [[ $2 ]]; do
		echo "$1"
		shift
	done
	echo -n "$1"
	
	(
	while [[ 1 ]]; do
		echo -n .
		sleep 0.3
	done
	) &
	
	ACTIVITY_PID=$!
}

# Stops the activity indicator
stop_activity()
{
	if (( $QUIET )); then
		return
	fi
	
	disown $ACTIVITY_PID
	kill $ACTIVITY_PID
	
	echo " ]"
}

TOTAL_HOSTS=0
RHOSTS=""
while [[ $1 =~ .@ ]]; do
	RHOSTS="$RHOSTS $1"
	shift
	((TOTAL_HOSTS++))
done
if (( ! $TOTAL_HOSTS )); then
	RHOSTS="will@vm-centos will@vm-debian will@vm-fedora will@vm-freebsd will@vm-netbsd will@vm-openbsd will@vm-opensolaris will@vm-opensuse will@osx will@vm-server2008 will@vm-ubuntu will@vm-xp"
	TOTAL_HOSTS=12
fi

# If we are adding keys, grab the current user's SSH public key
PUBLIC_KEY=""
if (( $ADD_KEY )); then
	if [[ -e ~/.ssh/id_rsa.pub ]]; then
		PUBLIC_KEY=$(cat ~/.ssh/id_rsa.pub)
		
	elif [[ -e ~/.ssh/id_dsa.pub ]]; then
		PUBLIC_KEY=$(cat ~/.ssh/id_dsa.pub)
		
	else
		echo "ERROR - No public key found in ~/.ssh/id_rsa.pub or ~/.ssh/id_dsa.pub"
		exit
	fi
fi

if (( ! $ADD_KEY && ! $SHOW_INFO && ! $SUB_CALL )); then
	MASTER_START=$(date +%s)
	start_activity "[ Creating tarball"
	tar --exclude='*/output/*' --exclude='*/.svn/*' --exclude='*/cachegrind.out.*' -czpf /tmp/flourish_local.tar.gz -C ../ classes tests
	stop_activity
fi

if (( $PARALLEL_DB )); then
		
	$0 -s $RHOSTS "$@" .text ':mysql*' '!postgresql*' '!mssql*' '!oracle*' '!db2*' > output/parallel.mysql.text &
	$0 -s $RHOSTS "$@" .text ':oracle*' '!mysql*' '!postgresql*' '!mssql*' '!db2*' > output/parallel.oracle.text &
	$0 -s $RHOSTS "$@" .text ':postgresql*' '!mysql*' '!mssql*' '!oracle*' '!db2*' > output/parallel.postgresql.text &
	$0 -s $RHOSTS "$@" .text ':mssql*' '!mysql*' '!postgresql*' '!oracle*' '!db2*' > output/parallel.mssql.text &
	$0 -s $RHOSTS "$@" .text '!mssql*' '!mysql*' '!postgresql*' '!oracle*' ':db2*' > output/parallel.db2.text &
	
	wait
	
	$0 -s -n $CONCURRENT $RHOSTS "$@" .text '!mysql*' '!postgresql*' '!mssql*' '!oracle*' '!db2*' > output/parallel.non-db.text &
	
	wait
	
	echo "{"
		
	I=0
	for RHOST in $RHOSTS; do
		PASSED=0
		FAILED=0
		SKIPPED=0
		PHP_ERRORS=0
		
		for FILE in output/parallel.mysql.text output/parallel.postgresql.text output/parallel.mssql.text output/parallel.oracle.text output/parallel.db2.text output/parallel.non-db.text; do
		#for FILE in output/parallel.mysql.text; do
			PASSED=$(( $PASSED + $(grep "$RHOST>passed=" "$FILE" | sed "s/$RHOST>passed=//") ))
			FAILED=$(( $FAILED + $(grep "$RHOST>failed=" "$FILE" | sed "s/$RHOST>failed=//") ))
			SKIPPED=$(( $SKIPPED + $(grep "$RHOST>skipped=" "$FILE" | sed "s/$RHOST>skipped=//") ))
			PHP_ERRORS=$(( $PHP_ERRORS + $(grep "$RHOST>php_errors=" "$FILE" | sed "s/$RHOST>php_errors=//") ))	
		done
		
		if (( $I != 0 )); then
			echo ','
		fi
		
		echo -n '    "'"$RHOST"'": {"passed": '"$PASSED"', "failed": '"$FAILED"', "skipped": '"$SKIPPED"', "php_errors": '"$PHP_ERRORS"'}'
		
		((I++))
	done
	
	echo
	echo "}"
	
	rm output/parallel.mysql.text
	rm output/parallel.postgresql.text
	rm output/parallel.mssql.text
	rm output/parallel.oracle.text
	rm output/parallel.db2.text
	rm output/parallel.non-db.text
	
	rm /tmp/flourish_local.tar.gz
	
	exit
fi
	
if (( $JSON && $TOTAL_HOSTS > 1 )); then
	printf "{"
fi

RHOST_NUM=0
for RHOST in $RHOSTS; do
	
	if (( $SHOW_INFO )); then
		queue_command uname -a
		queue_command echo '----'
		queue_command uname -v
		queue_command echo '----'
		queue_command which php phpunit sqsh mysql psql sqlplus db2batch convert \2\> /dev/null
		queue_command echo '----'
		queue_command php -m
		queue_command echo '----'
		queue_command php --version \| head -n 1 \| sed 's/^PHP \([0-9.]*\).*$/\1/'
		queue_command echo '----'
		queue_command phpunit --version \| sed 's/^[^0-9]*\([0-9.]*\) [^0-9]*/\1/g' \| head -n 1
		
		exec_commands $RHOST OUTPUT
		assign "$OUTPUT" ---- UNAME UNAME_V PROGRAMS PHP_MODULES PHP_VERSION PHPUNIT_VERSION
		
		UNAME=${UNAME/ $UNAME_V/}
		
		if (( $RHOST_NUM )); then
			echo
		fi
		echo -e "\033[1;37;44m$RHOST\033[0m:"
		show_required php     $(present "$PROGRAMS" '/php\>') $PHP_VERSION
		show_required phpunit $(present "$PROGRAMS" /phpunit) $PHPUNIT_VERSION
		EXT_NUM=0
		show_ext bcmath     $(present "$PHP_MODULES" bcmath)     0
		show_ext ctype      $(present "$PHP_MODULES" ctype)      1
		show_ext dom        $(present "$PHP_MODULES" dom)        1
		show_ext gd         $(present "$PHP_MODULES" gd)         0
		show_ext iconv      $(present "$PHP_MODULES" iconv)      1
		show_ext ibm_db2    $(present "$PHP_MODULES" ibm_db2)    0
		show_ext imap       $(present "$PHP_MODULES" imap)       0
		show_ext json       $(present "$PHP_MODULES" json)       0
		show_ext mbstring   $(present "$PHP_MODULES" mbstring)   0
		show_ext mcrypt     $(present "$PHP_MODULES" mcrypt)     0
		show_ext openssl    $(present "$PHP_MODULES" openssl)    0
		show_ext mssql      $(present "$PHP_MODULES" mssql)      0
		show_ext mysql      $(present "$PHP_MODULES" 'mysql\>')  0
		show_ext mysqli     $(present "$PHP_MODULES" mysqli)     0
		show_ext oci8       $(present "$PHP_MODULES" oci8)       0
		show_ext pdo_dblib  $(present "$PHP_MODULES" 'pdo_dblib\|pdo_mssql')  0
		show_ext pdo_ibm    $(present "$PHP_MODULES" pdo_ibm)    0
		show_ext pdo_mysql  $(present "$PHP_MODULES" pdo_mysql)  0
		show_ext pdo_oci    $(present "$PHP_MODULES" PDO_OCI)    0
		show_ext pdo_pgsql  $(present "$PHP_MODULES" pdo_pgsql)  0
		show_ext pdo_sqlite $(present "$PHP_MODULES" pdo_sqlite) 0
		show_ext pgsql      $(present "$PHP_MODULES" pgsql)      0
		show_ext sqlite     $(present "$PHP_MODULES" sqlite)     0
		show_ext sqlsrv     $(present "$PHP_MODULES" sqlsrv)     0
		if (( $EXT_NUM )); then
			echo
		fi
		
		show_db db2        $(present "$PHP_MODULES" ibm)            $(present "$PROGRAMS" /db2batch)
		show_db mysql      $(present "$PHP_MODULES" mysql)          $(present "$PROGRAMS" /mysql)
		show_db postgresql $(present "$PHP_MODULES" pgsql)          $(present "$PROGRAMS" /psql)
		show_db oracle     $(present "$PHP_MODULES" oci)            $(present "$PROGRAMS" /sqlplus)
		show_db mssql      $(present "$PHP_MODULES" 'mssql\|dblib\|sqlsrv') $(present "$PROGRAMS" /sqsh)
		
		show_status ImageMagick $(present "$PROGRAMS" /convert)
		continue
	fi
	
	
	if (( $ADD_KEY )); then
		queue_command \( \[ ! -e \~/.ssh \] \&\& ssh-keygen -q -f \~/.ssh/id_rsa -N '' \)
		queue_command echo "$PUBLIC_KEY" \>\> \~/.ssh/authorized_keys
		exec_commands $RHOST
		continue
	fi
	
	
	if (( $CONCURRENT == 1 )); then
		TOKEN=$(wget -O - -o /dev/null http://flourishlib.com/test_token.php?action=obtain)
		
		start_activity "[ Pushing to $RHOST"
		
		SSH_PORT=""
		SCP_PORT=""
		PORT=""
		if [[ $RHOST =~ : ]]; then
			PORT=${RHOST##*:}
			SSH_PORT="-p $PORT"
			SCP_PORT="-P $PORT"
			SSH_HOST=${RHOST%%:*}
		else
			SSH_HOST="$RHOST"
		fi
		REMOTE_INFO=$(ssh -A -q $SSH_PORT $SSH_HOST 'echo $SHELL && which mysql psql sqlplus sqsh db2batch' 2> /dev/null)
		REMOTE_SHELL=$(echo "$REMOTE_INFO" | awk 'BEGIN { RS=""; FS="\n" } {print $1}')
		if [[ $REMOTE_SHELL =~ csh ]]; then
			IS_CSH=1
		fi
		
		HAS_MYSQL=$(present "$REMOTE_INFO" /mysql)
		HAS_PSQL=$(present "$REMOTE_INFO" /psql)
		HAS_SQLPLUS=$(present "$REMOTE_INFO" /sqlplus)
		HAS_SQSH=$(present "$REMOTE_INFO" /sqsh)
		HAS_DB2BATCH=$(present "$REMOTE_INFO" /db2batch)
		
		scp $SCP_PORT -q /tmp/flourish_local.tar.gz $SSH_HOST:/tmp/flourish_$TOKEN.tar.gz
		
		queue_command cd /tmp/
		queue_command rm -Rf flourish_$TOKEN
		queue_command mkdir flourish_$TOKEN
		queue_command mv flourish_$TOKEN.tar.gz flourish_$TOKEN/
		queue_command cd flourish_$TOKEN
		queue_command tar xzf flourish_$TOKEN.tar.gz \2\> /dev/null

		CONFIG_EXCLUSIONS=""

		if (( ! $NO_MYSQL && $HAS_MYSQL )); then
			queue_command mysql -h db.flourishlib.com -u flourish -ppassword mysql -e "DROP DATABASE IF EXISTS flourish_$TOKEN; CREATE DATABASE flourish_$TOKEN CHARACTER SET 'utf8';"
		fi
		
		if (( ! $NO_POSTGRESQL && $HAS_PSQL )); then
			queue_command export PGPASSWORD='password'
			queue_command psql -q -h db.flourishlib.com -U flourish -d postgres -c "DROP DATABASE IF EXISTS flourish_$TOKEN;" \2\> /dev/null
			queue_command psql -q -h db.flourishlib.com -U flourish -d postgres -c "CREATE DATABASE flourish_$TOKEN ENCODING 'UTF-8';"
		fi
		
		if (( ! $NO_ORACLE && $HAS_SQLPLUS )); then
			queue_command echo "DROP USER flourish_$TOKEN CASCADE;
				DROP ROLE flourish_${TOKEN}_role;
				DROP USER flourish_${TOKEN}_2 CASCADE;
				CREATE USER flourish_$TOKEN IDENTIFIED BY password DEFAULT TABLESPACE users TEMPORARY TABLESPACE temp;
				GRANT CONNECT, RESOURCE, GRANT ANY PRIVILEGE, GRANT ANY OBJECT PRIVILEGE, UNLIMITED TABLESPACE, CREATE ROLE, DROP ANY ROLE, ALTER ANY ROLE, GRANT ANY ROLE, CREATE ANY TABLE, CREATE ANY INDEX, CREATE ANY SEQUENCE, CREATE ANY TRIGGER, ALTER ANY INDEX, ALTER ANY TRIGGER, ALTER ANY SEQUENCE, SELECT ANY SEQUENCE, DROP ANY TABLE, DROP ANY INDEX, DROP ANY SEQUENCE, DROP ANY TRIGGER TO flourish_$TOKEN;
				CREATE ROLE flourish_${TOKEN}_role;
				GRANT flourish_${TOKEN}_role TO flourish_$TOKEN;
				CREATE USER flourish_${TOKEN}_2 IDENTIFIED BY password DEFAULT TABLESPACE users TEMPORARY TABLESPACE temp;
				GRANT UNLIMITED TABLESPACE TO flourish_${TOKEN}_2;
				exit" \| sqlplus -S flourish/password@db.flourishlib.com/XE \> /dev/null
		fi
		
		if (( ! $NO_MSSQL && $HAS_SQSH )); then
			queue_command sqsh -b -U flourish -P password -S win-db.flourishlib.com:1122 -D flourish -L semicolon_hack=1 -C "IF EXISTS(SELECT name FROM sys.databases WHERE name = 'flourish_$TOKEN') DROP DATABASE flourish_$TOKEN;" \| awk 'BEGIN { RS="" } {gsub(".return status = 0.", "");printf $0}'
			queue_command sqsh -b -U flourish -P password -S win-db.flourishlib.com:1122 -D flourish -L semicolon_hack=1 -C "CREATE DATABASE flourish_$TOKEN;"
			queue_command sqsh -b -U flourish -P password -S win-db.flourishlib.com:1123 -D flourish -L semicolon_hack=1 -C "IF EXISTS(SELECT name FROM sys.databases WHERE name = 'flourish_$TOKEN') DROP DATABASE flourish_$TOKEN;"
			queue_command sqsh -b -U flourish -P password -S win-db.flourishlib.com:1123 -D flourish -L semicolon_hack=1 -C "CREATE DATABASE flourish_$TOKEN;"
		fi
		
		#if (( ! $NO_DB2 && $HAS_DB2BATCH )); then
			# Since database creation is very slow and users are controlled via the OS, everything is kept in place for DB2
		#fi

		if (( $SUB_CALL || $JSON || $TEXT )); then
			exec_commands $RHOST OUTPUT
		else
			exec_commands $RHOST
		fi

		stop_activity

		DEBUG_FLAG="";
		if (( $DEBUG_MODE )); then
			DEBUG_FLAG="@d"
		fi

		queue_command cd /tmp/flourish_$TOKEN/tests/
		queue_command export NLS_LANG="AMERICAN_AMERICA.AL32UTF8"
		queue_command export DB2CODEPAGE="1208"
		queue_command php tests.php \#flourish_$TOKEN $CONFIG_EXCLUSIONS "$@" $DEBUG_FLAG $FORMAT
		if (( $DEBUG_MODE )); then
			queue_command gzip -S .${SSH_HOST##*@}.gz /tmp/flourish_$TOKEN/tests/cachegrind.out.*
			queue_command echo "[ Debug shell - database is flourish_$TOKEN - type 'exit' to finish ]"
			queue_command bash
		fi
		
		if (( $SUB_CALL || $JSON || $TEXT )); then
			exec_commands $RHOST OUTPUT
			if (( $JSON && $TOTAL_HOSTS > 1 )); then
				SEP=""
				if (( $RHOST_NUM < $TOTAL_HOSTS - 1 )); then
					SEP=","
				fi
				printf "\n    "\""%s"\"": %s$SEP" ${RHOST%%:*} "$OUTPUT"
			else
				echo "$OUTPUT"
			fi
		else
			exec_commands $RHOST
		fi

		start_activity "[ Cleaning up"

		if (( $DEBUG_MODE )); then
			scp $SCP_PORT -q $SSH_HOST:/tmp/flourish_$TOKEN/tests/cachegrind.out.* ./
		fi

		queue_command rm -Rf /tmp/flourish_$TOKEN
		
		if (( ! $NO_MYSQL && $HAS_MYSQL )); then
			queue_command mysql -h db.flourishlib.com -u flourish -ppassword mysql -e "DROP DATABASE IF EXISTS flourish_$TOKEN;"
		fi
		
		if (( ! $NO_POSTGRESQL && $HAS_PSQL )); then
			queue_command export PGPASSWORD="password"
			queue_command psql -q -h db.flourishlib.com -U flourish -d postgres -c "DROP DATABASE IF EXISTS flourish_$TOKEN;" \2\> /dev/null
		fi
		
		if (( ! $NO_ORACLE && $HAS_SQLPLUS )); then
			queue_command echo "DROP USER flourish_$TOKEN CASCADE;
				DROP ROLE flourish_${TOKEN}_role;
				DROP USER flourish_${TOKEN}_2 CASCADE;
				exit" \| sqlplus -S flourish/password@db.flourishlib.com/XE \> /dev/null
		fi
		
		if (( ! $NO_MSSQL && $HAS_SQSH )); then
			queue_command sqsh -b -U flourish -P password -S win-db.flourishlib.com:1122 -D flourish -C "IF EXISTS(SELECT name FROM sys.databases WHERE name = 'flourish_$TOKEN') DROP DATABASE flourish_$TOKEN" \| awk 'BEGIN { RS="" } {gsub(".return status = 0.", "");printf $0}'
			queue_command sqsh -b -U flourish -P password -S win-db.flourishlib.com:1123 -D flourish -C "IF EXISTS(SELECT name FROM sys.databases WHERE name = 'flourish_$TOKEN') DROP DATABASE flourish_$TOKEN"
		fi
		
		#if (( ! $NO_DB2 && $HAS_DB2BATCH )); then
			# Since database creation is very slow and users are controlled via the OS, everything is kept in place for DB2
		#fi

		if (( $SUB_CALL || $JSON || $TEXT )); then
			exec_commands $RHOST OUTPUT
		else
			exec_commands $RHOST
		fi

		stop_activity

		wget -O - -o /dev/null http://flourishlib.com/test_token.php?action=release\&token=$TOKEN
	
	else
		while (( $(jobs -pr | wc -l) >= $CONCURRENT )); do
			sleep 1
		done
		
		# When we run tests on multiple machines, we run them in the background recursively
		if (( $JSON )); then
			SEP=""
			if (( $RHOST_NUM < $TOTAL_HOSTS - 1 )); then
				SEP=","
			fi
			printf "\n    "\""%s"\"": %s$SEP" ${RHOST%%:*} "$($0 -s $RHOST "$@")" &
		elif (( $TEXT )); then
			(
				OUT=$($0 -s $RHOST "$@")
				printf "%s" "$OUT" | awk -F "\n" '{print '\""$RHOST>"\"' $1}'
			) &
		else
			$0 -s $RHOST "$@" &
		fi
	fi
	
	((RHOST_NUM++))
done

if (( ! $ADD_KEY && ! $SHOW_INFO && ! $SUB_CALL && $TOTAL_HOSTS == 1 )); then
	rm /tmp/flourish_local.tar.gz
fi

# Here we take the output of tests from multiple servers and make one big JSON object
if (( ! $SHOW_INFO && ! $ADD_KEY && $TOTAL_HOSTS > 1 )); then
	wait
	if (( ! $SUB_CALL )); then
		rm /tmp/flourish_local.tar.gz
	fi
	if (( $JSON )); then
		printf "\n}\n"
	fi
fi

if (( ! $ADD_KEY && ! $SHOW_INFO && ! $SUB_CALL && $TOTAL_HOSTS > 1 && ! $QUIET )); then
	MASTER_END=$(date +%s)
	RUNTIME=$((MASTER_END - MASTER_START))
	RUNTIME_HOURS=$((RUNTIME/3600%60))
	RUNTIME_MINUTES=$((RUNTIME/60%60))
	RUNTIME_SECONDS=$((RUNTIME%60))
	echo -n "["
	if (( $RUNTIME_HOURS )); then
		echo -n " $RUNTIME_HOURS hour"
		if (( $RUNTIME_HOURS != 1 )); then
			echo -n "s"
		fi
	fi
	if (( $RUNTIME_MINUTES )); then
		echo -n " $RUNTIME_MINUTES minute"
		if (( $RUNTIME_MINUTES != 1 )); then
			echo -n "s"
		fi
	fi
	if (( $RUNTIME_SECONDS )); then
		echo -n " $RUNTIME_SECONDS second"
		if (( $RUNTIME_SECONDS != 1 )); then
			echo -n "s"
		fi
	fi
	echo " total ]"
fi