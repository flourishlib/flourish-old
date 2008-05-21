<?php
$phpunit = trim(`which phpunit`);

// This should be made more robust at some point
if (empty($phpunit) && file_exists('/usr/bin/phpunit') && is_executable('/usr/bin/phpunit')) {
	$phpunit = '/usr/bin/phpunit'; 		
}

$revision = NULL;
$class    = NULL;
if (!empty($_SERVER['argc'])) {
	if (is_numeric($_SERVER['argv'][1])) {
		$revision = $_SERVER['argv'][1];	
	} else {
		$class = $_SERVER['argv'][1]; 		
	}
}

$class_root = './classes/';
if ($class && file_exists($class_root . $class)) {
	$class_dirs = array($class);
} else {
	$class_dirs = array_diff(scandir($class_root), array('.', '..', '.svn'));
}

$results = array();
$failures = 0;
$total_tests = 0;

foreach ($class_dirs as $class_dir) {
	$class_path  = $class_root . $class_dir;
	$class_tests = array_diff(scandir($class_path), array('.', '..', '.svn'));
	
	foreach ($class_tests as $class_test) {
		if (!preg_match('#^.*Test.*\.php$#', $class_test)) {
			continue;	
		}
		
		$test_name = preg_replace('#\.php$#i', '', $class_test);
		$test_file = $class_path . '/' . $class_test;
		
		$result = trim(`$phpunit --tap --log-xml ./xml $test_name $test_file`);	
		
		if (!$revision && stripos($result, 'Fatal error')) {
			echo "\033[0;37;41m";
			echo "FATAL ERROR";
			echo "\033[0m\n\n";
			$lines = explode("\n", $result);
			$lines = array_slice($lines, 3);
			echo join("\n", $lines) . "\n";
			exit;
		}
		
		$xml = new SimpleXMLElement(file_get_contents('./xml'), LIBXML_NOCDATA);
		unlink('./xml');
		
		$result = preg_replace('#^PHPUnit.*?$\s+\d+\.\.\d+\s+#ims', '', $result);
		$result = preg_replace('/\s*^# TestSuite "\w+" (ended|started)\.\s*$\s*/ims', "", $result);
		
		preg_match_all('#^(ok|not ok) (\d+) - (?:Failure: )?(\w+)\((\w+)\)$#ims', $result, $matches, PREG_SET_ORDER);
		
		$result = '';
		foreach ($matches as $match) {
			$result = array();
			$result['success'] = ($match[1] == 'ok') ? TRUE : FALSE;
			$result['name'] = $match[4] . '::' . $match[3] . "()";
			
			if ($match[1] != 'ok') {
				list($error_message) = (array) $xml->testsuite->testcase[($match[2]-1)]->failure;
				$lines = explode("\n", $error_message);
				$lines = array_slice($lines, 1, count($lines)-2);
				$result['error'] = join("\n", $lines);
				$failures++;
			}
			
			$results[] = $result;
			$total_tests++;
		}
	}
}


// If a revision is passed the output is for the unit test page on the site, so let's make some html
if ($revision) {
	
	?>
	<div class="revision <?= ($failures == 0) ? 'success' : 'failure'  ?>">
		<h2 class="revision_number">Revision <?= $revision ?></h2>
		<div class="successful_tests">
			<span class="tally"><?= $total_tests-$failures ?>/<?= $total_tests ?></span>
			<span class="description">tests succeeded</span>
		</div>
		<a class="download_link" href="/download/unit_tests/flourish_unit_tests_r<?= $revision ?>.zip">Download Unit Tests</a>
		<ul class="detailed_results"><?
		foreach ($results as $result) {
			echo '<li class="' . (($result['success']) ? 'success' : 'failure') . '"><strong>' . (($result['success']) ? 'Success' : 'Failure') . "</strong> " . $result['name'];
			if (!$result['success']) {
				?><div class="error_message"><?= nl2br($result['error']) ?></div><?	
			}
			echo "</li>\n";	
		}
		?></ul>
	</div>
	<?

	
// If the script was called without a revision number, this is probably being used during developement, so we format for terminal output
} else {
	
	if ($failures) { echo "\n"; }
	foreach ($results as $result) {
		if (!$result['success']) {
			echo "FAILURE in " . $result['name'] . "\n";
			$lines = explode("\n", $result['error']);
			foreach ($lines as $line) {
				echo "  " . $line . "\n";	
			}
			echo "\n--------\n";
		}
	}	
	if ($failures) { echo "\n"; }
	echo ($failures == 0) ? "\033[0;37;43m" : "\033[0;37;41m";
	echo ($total_tests-$failures) . '/' . $total_tests . " TESTS SUCCEEDED\033[0m\n";
	if ($failures) { echo "\n"; }
}      