<?php
include_once './support/constants.php';

define('DB_TYPE',     'sqlite');
define('DB',          ':memory:');
define('DB_USERNAME', NULL);
define('DB_PASSWORD', NULL);
define('DB_HOST',     NULL);
define('DB_PORT',     NULL);

define('DB_SETUP_FILE',    './database/setup.sqlite.sql');
define('DB_POPULATE_FILE', './database/populate.sqlite.sql');
define('DB_WIPE_FILE',     './database/wipe.sqlite.sql');
define('DB_TEARDOWN_FILE', './database/teardown.sqlite.sql');
define('DB_SCHEMA_FILE',   './database/schema.sqlite.json');

define('DB_EXTENDED_SETUP_FILE',    './database/setup-extended.sqlite.sql');
define('DB_EXTENDED_POPULATE_FILE', './database/populate-extended.sqlite.sql');
define('DB_EXTENDED_WIPE_FILE',     './database/wipe-extended.sqlite.sql');
define('DB_EXTENDED_TEARDOWN_FILE', './database/teardown-extended.sqlite.sql');

define('DB_DATATYPES_SETUP_FILE',    './database/setup-datatypes.sqlite.sql');
define('DB_DATATYPES_TEARDOWN_FILE', './database/teardown-datatypes.sqlite.sql');

define('DB_2',          ':memory:');
define('DB_2_USERNAME', NULL);
define('DB_2_PASSWORD', NULL);
define('DB_2_HOST',     NULL);
define('DB_2_PORT',     NULL);

define('DB_2_SETUP_FILE',    './database/setup-second_db.sqlite.sql');
define('DB_2_POPULATE_FILE', './database/populate-second_db.sqlite.sql');
define('DB_2_WIPE_FILE',     './database/wipe-second_db.sqlite.sql');
define('DB_2_TEARDOWN_FILE', './database/teardown-second_db.sqlite.sql');