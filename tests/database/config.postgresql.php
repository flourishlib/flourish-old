<?php
define('DB_TYPE',     'postgresql');
define('DB',          'flourish');
define('DB_USERNAME', 'flourish');
define('DB_PASSWORD', 'password');
define('DB_HOST',     'db.flourishlib.com');
define('DB_PORT',     NULL);

define('DB_SETUP_FILE',    './database/setup.postgresql.sql');
define('DB_TEARDOWN_FILE', './database/teardown.postgresql.sql');
define('DB_SCHEMA_FILE',   './database/schema.postgresql.json');

define('DB_EXTENDED_SETUP_FILE',    './database/setup-extended.postgresql.sql');
define('DB_EXTENDED_TEARDOWN_FILE', './database/teardown-extended.postgresql.sql');

define('DB_ALTERNATE_SCHEMA_SETUP_FILE',    './database/setup-alternate_schema.postgresql.sql');
define('DB_ALTERNATE_SCHEMA_TEARDOWN_FILE', './database/teardown-alternate_schema.postgresql.sql');
define('DB_ALTERNATE_SCHEMA_SCHEMA_FILE', './database/schema-alternate_schema.postgresql.json');

define('DB_DATATYPES_SETUP_FILE',    './database/setup-datatypes.postgresql.sql');
define('DB_DATATYPES_TEARDOWN_FILE', './database/teardown-datatypes.postgresql.sql');