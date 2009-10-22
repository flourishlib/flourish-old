<?php
define('DB_TYPE',     'mysql');
define('DB',          'flourish');
define('DB_USERNAME', 'flourish');
define('DB_PASSWORD', 'password');
define('DB_HOST',     'db.flourishlib.com');
define('DB_PORT',     NULL);

define('DB_SETUP_FILE',    './database/setup.mysql.sql');
define('DB_TEARDOWN_FILE', './database/teardown.mysql.sql');
define('DB_SCHEMA_FILE',   './database/schema.mysql.json');

define('DB_EXTENDED_SETUP_FILE',    './database/setup-extended.mysql.sql');
define('DB_EXTENDED_TEARDOWN_FILE', './database/teardown-extended.mysql.sql');

define('DB_DATATYPES_SETUP_FILE',    './database/setup-datatypes.mysql.sql');
define('DB_DATATYPES_TEARDOWN_FILE', './database/teardown-datatypes.mysql.sql');