<?php
include_once './support/constants.php';

define('DB_TYPE',     'sqlite');
define('DB',          ':memory:');
define('DB_USERNAME', NULL);
define('DB_PASSWORD', NULL);
define('DB_HOST',     NULL);
define('DB_PORT',     NULL);

define('DB_SETUP_FILE',    './database/setup-mapped_tables.sqlite.sql');
define('DB_POPULATE_FILE', './database/populate-mapped_tables.sqlite.sql');
define('DB_WIPE_FILE',     './database/wipe-mapped_tables.sqlite.sql');
define('DB_TEARDOWN_FILE', './database/teardown-mapped_tables.sqlite.sql');
define('DB_SCHEMA_FILE',   './database/schema.sqlite.json');

define('DB_EXTENDED_SETUP_FILE',    './database/setup-mapped_tables-extended.sqlite.sql');
define('DB_EXTENDED_POPULATE_FILE', './database/populate-extended.sqlite.sql');
define('DB_EXTENDED_WIPE_FILE',     './database/wipe-extended.sqlite.sql');
define('DB_EXTENDED_TEARDOWN_FILE', './database/teardown-extended.sqlite.sql');

define('MAP_TABLES', TRUE);