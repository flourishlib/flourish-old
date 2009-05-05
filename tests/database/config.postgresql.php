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