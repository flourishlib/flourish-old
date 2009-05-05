<?php
define('DB_TYPE',     'mssql');
define('DB',          'dsn:flourish-mssql');
define('DB_USERNAME', 'flourish');
define('DB_PASSWORD', 'password');
define('DB_HOST',     NULL);
define('DB_PORT',     NULL);

define('DB_SETUP_FILE',    './database/setup.mssql.sql');
define('DB_TEARDOWN_FILE', './database/teardown.mssql.sql');
define('DB_SCHEMA_FILE',   './database/schema.mssql.json');