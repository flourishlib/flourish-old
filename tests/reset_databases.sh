#!/bin/sh

mysql -h db.flourishlib.com -u flourish --password=password flourish < database/teardown-extended.mysql.sql
mysql -h db.flourishlib.com -u flourish --password=password flourish < database/teardown-datatypes.mysql.sql
mysql -h db.flourishlib.com -u flourish --password=password flourish < database/teardown.mysql.sql



PGPASSWORD="password"
export PGPASSWORD

psql -h db.flourishlib.com -U flourish -f database/teardown-extended.postgresql.sql > /dev/null 2>&1

psql -h db.flourishlib.com -U flourish -f database/teardown-alternate_schema.postgresql.sql > /dev/null 2>&1

psql -h db.flourishlib.com -U flourish -f database/teardown-datatypes.postgresql.sql > /dev/null 2>&1

psql -h db.flourishlib.com -U flourish -f database/teardown.postgresql.sql > /dev/null 2>&1



sqlplus -S flourish/password@db.flourishlib.com/XE "@database/teardown-extended.oracle.sql" > /dev/null <<ENDSQL
exit
ENDSQL

sqlplus -S flourish/password@db.flourishlib.com/XE "@database/teardown-alternate_schema.oracle.sql" > /dev/null <<ENDSQL
exit
ENDSQL

sqlplus -S flourish/password@db.flourishlib.com/XE "@database/teardown-datatypes.oracle.sql" > /dev/null <<ENDSQL
exit
ENDSQL

sqlplus -S flourish/password@db.flourishlib.com/XE "@database/teardown.oracle.sql" > /dev/null <<ENDSQL
exit
ENDSQL



sqsh -b -U flourish -P "password" -S win-db.flourishlib.com:1122 -L semicolon_hack=1 -i database/teardown-extended.mssql.sql  2> /dev/null
sqsh -b -U flourish -P "password" -S win-db.flourishlib.com:1122 -L semicolon_hack=1 -i database/teardown-alternate_schema.mssql.sql  2> /dev/null
sqsh -b -U flourish -P "password" -S win-db.flourishlib.com:1122 -L semicolon_hack=1 -i database/teardown-datatypes.mssql.sql  2> /dev/null
sqsh -b -U flourish -P "password" -S win-db.flourishlib.com:1122 -L semicolon_hack=1 -i database/teardown.mssql.sql  2> /dev/null



sqsh -b -U flourish -P "password" -S win-db.flourishlib.com:1123 -L semicolon_hack=1 -i database/teardown-extended.mssql.sql  2> /dev/null
sqsh -b -U flourish -P "password" -S win-db.flourishlib.com:1123 -L semicolon_hack=1 -i database/teardown-alternate_schema.mssql.sql  2> /dev/null
sqsh -b -U flourish -P "password" -S win-db.flourishlib.com:1123 -L semicolon_hack=1 -i database/teardown-datatypes.mssql.sql  2> /dev/null
sqsh -b -U flourish -P "password" -S win-db.flourishlib.com:1123 -L semicolon_hack=1 -i database/teardown.mssql.sql  2> /dev/null