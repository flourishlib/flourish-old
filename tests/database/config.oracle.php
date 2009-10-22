<?php
define('DB_TYPE',     'oracle');
define('DB',          'XE');
define('DB_USERNAME', 'flourish');
define('DB_PASSWORD', 'password');
define('DB_HOST',     'db.flourishlib.com');
define('DB_PORT',     NULL);

define('DB_SETUP_FILE',    './database/setup.oracle.sql');
define('DB_TEARDOWN_FILE', './database/teardown.oracle.sql');
define('DB_SCHEMA_FILE',   './database/schema.oracle.json');

define('DB_EXTENDED_SETUP_FILE',    './database/setup-extended.oracle.sql');
define('DB_EXTENDED_TEARDOWN_FILE', './database/teardown-extended.oracle.sql');

define('DB_ALTERNATE_SCHEMA_SETUP_FILE',    './database/setup-alternate_schema.oracle.sql');
define('DB_ALTERNATE_SCHEMA_TEARDOWN_FILE', './database/teardown-alternate_schema.oracle.sql');
define('DB_ALTERNATE_SCHEMA_SCHEMA_FILE', './database/schema-alternate_schema.oracle.json');

define('DB_DATATYPES_SETUP_FILE',    './database/setup-datatypes.oracle.sql');
define('DB_DATATYPES_TEARDOWN_FILE', './database/teardown-datatypes.oracle.sql');