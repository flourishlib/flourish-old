<?php
require_once './support/init.php';
eval($_SERVER['argv'][1]);
ob_end_clean();
echo 'completed';