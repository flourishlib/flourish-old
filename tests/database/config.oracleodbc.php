<?php
define('DB_TYPE',     'oracle');
define('DB',          'dsn:flourish-oracle');
define('DB_USERNAME', 'flourish');
define('DB_PASSWORD', 'password');
define('DB_HOST',     NULL);
define('DB_PORT',     NULL);

define('DB_SETUP_FILE',    './database/setup.oracle.sql');
define('DB_TEARDOWN_FILE', './database/teardown.oracle.sql');
define('DB_SCHEMA_FILE',   './database/schema.oracle.json');