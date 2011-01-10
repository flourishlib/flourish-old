#!/bin/sh

TYPE=""
DB="flourish"

if [ "$1" = "-t" ]; then
	TYPE="$2"
	shift 2
fi
if [ "$1" != "" ]; then
	DB="$1"
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "db2" ]; then
	if [ "$DB" = "flourish" ]; then
		DB2_DB="flourish"
	else
		DB2_DB=$(echo "$DB" | sed 's/ourish//')
	fi
	db2batch  -s off -time off -d fl -a $DB2_DB/password -f database/teardown-extended.db2.sql > /dev/null 2>&1
	#db2batch  -s off -time off -d fl -a $DB2_DB/password -f database/teardown-datatypes.db2.sql > /dev/null 2>&1
	db2batch  -s off -time off -d fl -a $DB2_DB/password -f database/teardown.db2.sql > /dev/null 2>&1
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "mysql" ]; then
	mysql -h db.flourishlib.com -u flourish --password=password $DB < database/teardown-extended.mysql.sql
	#mysql -h db.flourishlib.com -u flourish --password=password $DB < database/teardown-datatypes.mysql.sql
	mysql -h db.flourishlib.com -u flourish --password=password $DB < database/teardown.mysql.sql
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "pgsql" ]; then
	export PGPASSWORD="password"
	psql -h db.flourishlib.com -U flourish -d $DB -f database/teardown-extended.postgresql.sql > /dev/null 2>&1
	psql -h db.flourishlib.com -U flourish -d $DB -f database/teardown-alternate_schema.postgresql.sql > /dev/null 2>&1
	#psql -h db.flourishlib.com -U flourish -d $DB -f database/teardown-datatypes.postgresql.sql > /dev/null 2>&1
	psql -h db.flourishlib.com -U flourish -d $DB -f database/teardown.postgresql.sql > /dev/null 2>&1
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "oracle" ]; then
	echo "exit" | sqlplus -S $DB/password@db.flourishlib.com/XE "@database/teardown-extended.oracle.sql" > /dev/null
	echo "exit" | sqlplus -S $DB/password@db.flourishlib.com/XE "@database/teardown-alternate_schema.oracle.sql" > /dev/null
	#echo "exit" | sqlplus -S $DB/password@db.flourishlib.com/XE "@database/teardown-datatypes.oracle.sql" > /dev/null
	echo "exit" | sqlplus -S $DB/password@db.flourishlib.com/XE "@database/teardown.oracle.sql" > /dev/null
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "mssql" ]; then
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1122 -D $DB -L semicolon_hack=1 -i database/teardown-extended.mssql.sql > /dev/null 2> /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1122 -D $DB -L semicolon_hack=1 -i database/teardown-alternate_schema.mssql.sql > /dev/null 2> /dev/null
	#sqsh -U flourish -P "password" -S win-db.flourishlib.com:1122 -D $DB -L semicolon_hack=1 -i database/teardown-datatypes.mssql.sql > /dev/null 2> /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1122 -D $DB -L semicolon_hack=1 -i database/teardown.mssql.sql > /dev/null 2> /dev/null
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "mssql2008" ]; then
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1123 -D $DB -L semicolon_hack=1 -i database/teardown-extended.mssql.sql > /dev/null 2> /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1123 -D $DB -L semicolon_hack=1 -i database/teardown-alternate_schema.mssql.sql > /dev/null 2> /dev/null
	#sqsh -U flourish -P "password" -S win-db.flourishlib.com:1123 -D $DB -L semicolon_hack=1 -i database/teardown-datatypes.mssql.sql > /dev/null 2> /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1123 -D $DB -L semicolon_hack=1 -i database/teardown.mssql.sql > /dev/null 2> /dev/null
fi
