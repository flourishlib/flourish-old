<?php
include_once './support/constants.php';

define('DB_TYPE',     'db2');
define('DB',          'fl');
define('DB_USERNAME', DB_NAME == 'flourish' ? 'flourish' : str_replace('flourish', 'fl', DB_NAME));
define('DB_PASSWORD', 'password');
define('DB_HOST',     'db.flourishlib.com');
define('DB_PORT',     3700);

define('DB_SETUP_FILE',    './database/setup.db2.sql');
define('DB_TEARDOWN_FILE', './database/teardown.db2.sql');
define('DB_SCHEMA_FILE',   './database/schema.db2.json');

define('DB_EXTENDED_SETUP_FILE',    './database/setup-extended.db2.sql');
define('DB_EXTENDED_TEARDOWN_FILE', './database/teardown-extended.db2.sql');

define('DB_ALTERNATE_SCHEMA_SETUP_FILE',    './database/setup-alternate_schema.db2.sql');
define('DB_ALTERNATE_SCHEMA_TEARDOWN_FILE', './database/teardown-alternate_schema.db2.sql');
define('DB_ALTERNATE_SCHEMA_SCHEMA_FILE', './database/schema-alternate_schema.db2.json');

define('DB_DATATYPES_SETUP_FILE',    './database/setup-datatypes.db2.sql');
define('DB_DATATYPES_TEARDOWN_FILE', './database/teardown-datatypes.db2.sql');

define('DB_SECOND_SCHEMA', DB_NAME == 'flourish' ? 'flourish2' : DB_USERNAME . '_2');

if (!defined('SKIPPING')) {
	$db_name = DB_USERNAME;
	`sh reset_databases.sh -t db2 $db_name`;
}