#!/bin/bash

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
		SCHEMA_REPLACE="flourish2"
	else
		DB2_DB=$(echo "$DB" | sed 's/ourish//')
		SCHEMA_REPLACE="${DB2_DB}_2"
	fi
	
	db2batch  -s off -time off -d fl -a $DB2_DB/password -f database/teardown-extended.db2.sql > /dev/null 2>&1
	cp database/teardown-alternate_schema.db2.sql database/tmp.sql
	sed -i -e "s/flourish2./$SCHEMA_REPLACE./g" database/tmp.sql
	db2batch  -s off -time off -d fl -a $DB2_DB/password -f database/tmp.sql > /dev/null 2>&1
	db2batch  -s off -time off -d fl -a $DB2_DB/password -f database/teardown.db2.sql > /dev/null 2>&1

	db2batch  -s off -time off -d fl -a $DB2_DB/password -f database/setup.db2.sql > /dev/null 2>&1
	cp database/setup-alternate_schema.db2.sql database/tmp.sql
	sed -i -e "s/flourish2./$SCHEMA_REPLACE./g" database/tmp.sql
	db2batch  -s off -time off -d fl -a $DB2_DB/password -f database/tmp.sql > /dev/null 2>&1
	db2batch  -s off -time off -d fl -a $DB2_DB/password -f database/setup-extended.db2.sql > /dev/null 2>&1

	rm database/tmp.sql
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "mysql" ]; then
	mysql -h db.flourishlib.com -u flourish --password=password $DB < database/teardown-extended.mysql.sql
	mysql -h db.flourishlib.com -u flourish --password=password $DB < database/teardown.mysql.sql

	mysql -h db.flourishlib.com -u flourish --password=password $DB < database/setup.mysql.sql
	mysql -h db.flourishlib.com -u flourish --password=password $DB < database/setup-extended.mysql.sql
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "pgsql" ]; then
	export PGPASSWORD="password"
	psql -h db.flourishlib.com -U flourish -d $DB -f database/teardown-extended.postgresql.sql > /dev/null 2>&1
	psql -h db.flourishlib.com -U flourish -d $DB -f database/teardown-alternate_schema.postgresql.sql > /dev/null 2>&1
	psql -h db.flourishlib.com -U flourish -d $DB -f database/teardown.postgresql.sql > /dev/null 2>&1

	psql -h db.flourishlib.com -U flourish -d $DB -f database/setup.postgresql.sql > /dev/null 2>&1
	psql -h db.flourishlib.com -U flourish -d $DB -f database/setup-alternate_schema.postgresql.sql > /dev/null 2>&1
	psql -h db.flourishlib.com -U flourish -d $DB -f database/setup-extended.postgresql.sql > /dev/null 2>&1
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "oracle" ]; then
	if [ "$DB" = "flourish" ]; then
		SCHEMA_REPLACE="flourish2"
		ROLE_REPLACE="flourish2"
	else
		SCHEMA_REPLACE="${DB}_2"
		ROLE_REPLACE="${DB}_role"
	fi

	echo "exit" | sqlplus -S $DB/password@db.flourishlib.com/XE "@database/teardown-extended.oracle.sql" > /dev/null
	cp database/teardown-alternate_schema.oracle.sql database/tmp.sql
	sed -i -e "s/flourish2./$SCHEMA_REPLACE./g" -e "s/flourish_role/$ROLE_REPLACE/g" database/tmp.sql
	echo "exit" | sqlplus -S $DB/password@db.flourishlib.com/XE "@database/tmp.sql" > /dev/null
	echo "exit" | sqlplus -S $DB/password@db.flourishlib.com/XE "@database/teardown.oracle.sql" > /dev/null

	cp database/setup.oracle.sql database/tmp.sql
	sed -i -e "s/\\\\;/;/g" -e 's/^;/\//' database/tmp.sql
	echo "exit" | sqlplus -S $DB/password@db.flourishlib.com/XE "@database/tmp.sql" > /dev/null
	
	cp database/setup-alternate_schema.oracle.sql database/tmp.sql
	sed -i -e "s/flourish2./$SCHEMA_REPLACE./g" -e "s/flourish_role/$ROLE_REPLACE/g" -e "s/\\\\;/;/g" -e 's/^;/\//' database/tmp.sql
	echo "exit" | sqlplus -S $DB/password@db.flourishlib.com/XE "@database/tmp.sql" > /dev/null

	cp database/setup-extended.oracle.sql database/tmp.sql
	sed -i -e "s/\\\\;/;/g" -e 's/^;/\//' database/tmp.sql
	echo "exit" | sqlplus -S $DB/password@db.flourishlib.com/XE "@database/tmp.sql" > /dev/null

	rm database/tmp.sql
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "mssql" ]; then
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1122 -D $DB -L semicolon_hack=1 -i database/teardown-extended.mssql.sql > /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1122 -D $DB -L semicolon_hack=1 -i database/teardown-alternate_schema.mssql.sql > /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1122 -D $DB -L semicolon_hack=1 -i database/teardown.mssql.sql > /dev/null

	if [[ $(uname -o) == Cygwin ]]; then
		sed -r -i -e "s/(CREATE SCHEMA \w+;)/\1\nGO/g" database/setup-alternate_schema.mssql.sql
	fi

	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1122 -D $DB -L semicolon_hack=1 -i database/setup.mssql.sql > /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1122 -D $DB -L semicolon_hack=1 -i database/setup-alternate_schema.mssql.sql > /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1122 -D $DB -L semicolon_hack=1 -i database/setup-extended.mssql.sql > /dev/null
fi

if [ "$TYPE" = "" ] || [ "$TYPE" = "mssql2008" ]; then
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1123 -D $DB -L semicolon_hack=1 -i database/teardown-extended.mssql.sql > /dev/null 2> /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1123 -D $DB -L semicolon_hack=1 -i database/teardown-alternate_schema.mssql.sql > /dev/null 2> /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1123 -D $DB -L semicolon_hack=1 -i database/teardown.mssql.sql > /dev/null 2> /dev/null
	
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1123 -D $DB -L semicolon_hack=1 -i database/setup.mssql.sql > /dev/null 2> /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1123 -D $DB -L semicolon_hack=1 -i database/setup-alternate_schema.mssql.sql > /dev/null 2> /dev/null
	sqsh -U flourish -P "password" -S win-db.flourishlib.com:1123 -D $DB -L semicolon_hack=1 -i database/setup-extended.mssql.sql > /dev/null 2> /dev/null
fi
