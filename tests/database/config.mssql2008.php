<?php
include_once './support/constants.php';

define('DB_TYPE',     'mssql');
define('DB',          DB_NAME);
define('DB_USERNAME', 'flourish');
define('DB_PASSWORD', 'password');
define('DB_HOST',     'win-db.flourishlib.com');
define('DB_PORT',     1123);

define('DB_SETUP_FILE',    './database/setup.mssql2008.sql');
define('DB_TEARDOWN_FILE', './database/teardown.mssql.sql');
define('DB_SCHEMA_FILE',   './database/schema.mssql2008.json');

define('DB_EXTENDED_SETUP_FILE',    './database/setup-extended.mssql2008.sql');
define('DB_EXTENDED_TEARDOWN_FILE', './database/teardown-extended.mssql.sql');

define('DB_DATATYPES_SETUP_FILE',    './database/setup-datatypes.mssql2008.sql');
define('DB_DATATYPES_TEARDOWN_FILE', './database/teardown-datatypes.mssql.sql');

if (!defined('SKIPPING')) {
	$db_name = DB_NAME;
	`sh reset_databases.sh -t mssql2008 $db_name`;
}