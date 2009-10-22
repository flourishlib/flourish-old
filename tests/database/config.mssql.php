<?php
define('DB_TYPE',     'mssql');
define('DB',          'flourish');
define('DB_USERNAME', 'flourish');
define('DB_PASSWORD', 'password');
define('DB_HOST',     'db.flourishlib.com');
define('DB_PORT',     1122);

define('DB_SETUP_FILE',    './database/setup.mssql.sql');
define('DB_TEARDOWN_FILE', './database/teardown.mssql.sql');
define('DB_SCHEMA_FILE',   './database/schema.mssql.json');

define('DB_EXTENDED_SETUP_FILE',    './database/setup-extended.mssql.sql');
define('DB_EXTENDED_TEARDOWN_FILE', './database/teardown-extended.mssql.sql');

define('DB_ALTERNATE_SCHEMA_SETUP_FILE',    './database/setup-alternate_schema.mssql.sql');
define('DB_ALTERNATE_SCHEMA_TEARDOWN_FILE', './database/teardown-alternate_schema.mssql.sql');
define('DB_ALTERNATE_SCHEMA_SCHEMA_FILE', './database/schema-alternate_schema.mssql.json');

define('DB_DATATYPES_SETUP_FILE',    './database/setup-datatypes.mssql.sql');
define('DB_DATATYPES_TEARDOWN_FILE', './database/teardown-datatypes.mssql.sql');