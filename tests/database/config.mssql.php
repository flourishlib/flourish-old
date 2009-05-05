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