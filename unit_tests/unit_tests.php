#!/usr/bin/php
<?php
$phpunit = trim(`which phpunit`);
$phpbin  = trim(`which php`);


// This should be made more robust at some point
if (empty($phpunit) && file_exists('/usr/bin/phpunit') && is_executable('/usr/bin/phpunit')) {
	$phpunit = '/usr/bin/phpunit'; 		
}
if (empty($phpbin) && file_exists('/usr/bin/php') && is_executable('/usr/bin/php')) {
	$phpbin = '/usr/bin/php'; 		
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

		$configs = array();
		if (file_exists($class_path . '/.configs')) {
			$options = file($class_path . '/.configs');
			foreach ($options as $option) {
				list($ext, $config) = explode(';', $option);
				if (!$ext || extension_loaded($ext)) {
					$configs[$ext] = trim($config);	
				}
			}	
		} else {
			$configs[''] = '';	
		}
		
		
		foreach ($configs as $ext => $config) {
			$result = trim(`$phpbin $config $phpunit --tap --log-xml ./xml $test_name $test_file`);	
			
			if (!$revision && stripos($result, 'Fatal error')) {
				echo "\033[0;37;41m";
				echo "FATAL ERROR";
				echo "\033[0m\n\n";
				$lines = explode("\n", $result);
				$lines = array_slice($lines, 3);
				echo join("\n", $lines) . "\n";
				exit;
			}
			
			try {
				$xml = new SimpleXMLElement(file_get_contents('./xml'), LIBXML_NOCDATA);
				unlink('./xml');
			} catch (Exception $e) {
				echo $result;
				echo "\n\033[0;37;41mPHP ERROR\033[0m\n";
				die;
			}

			$result = preg_replace('#^PHPUnit.*?$\s+\d+\.\.\d+\s+#ims', '', $result);
			$result = preg_replace('/\s*^# TestSuite "\w+" (ended|started)\.\s*$\s*/ims', "", $result);
			
			preg_match_all('#<pre class="exposed">(.*?)</pre>#ims', $result, $matches);
			
			foreach ($matches[1] as $match) {
				echo $match . "\n";	
			}
			
			$result = preg_replace('#<pre class="exposed">.*?</pre>#ims', '', $result);
			
			preg_match_all('#^(ok|not ok) (\d+) - (Failure: |Error: )?(\w+)\((\w+)\)( with data set \#\d+ [^\n]*)?$#ims', $result, $matches, PREG_SET_ORDER);
			
			$result = '';
			foreach ($matches as $match) {
				$result = array();
				$result['success'] = ($match[1] == 'ok') ? TRUE : FALSE;
				$result['name'] = $match[5] . '::' . $match[4] . "()" . (isset($match[6]) ? $match[6] : '');
				
				if ($match[1] != 'ok') {
					$testcase_idx = $match[2]-1;
					$counter = 0;
					$testcase = NULL;
					if (sizeof((array) $xml->testsuite) > 1) {
						foreach ($xml->testsuite->testsuite as $testsuite) {
							foreach ($testsuite->testcase as $_testcase) {
								if ($counter == $testcase_idx) {
									$testcase = $_testcase;
									break 2;	
								}
								$counter++;
							}
						}
					} else {
						$testcase = $xml->testsuite->testcase[$testcase_idx];
					}
					
					
					if ($match[3] == 'Failure: ') {
						list($error_message) = (array) $testcase->failure;
						$lines = explode("\n", $error_message);
						$lines = array_slice($lines, 1, count($lines)-2);
					} else {
						list($error_message) = (array) $testcase->error;
						$lines = explode("\n", $error_message);
						$lines = array_slice($lines, 1, count($lines)-2);
					}
					$result['error'] = join("\n", $lines);
					$failures++;
				}
				
				$results[] = $result;
				$total_tests++;
			}
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
		<?
		/*<ul class="detailed_results"><?
		foreach ($results as $result) {
			echo '<li class="' . (($result['success']) ? 'success' : 'failure') . '"><strong>' . (($result['success']) ? 'Success' : 'Failure') . "</strong> " . $result['name'];
			if (!$result['success']) {
				?><div class="error_message"><?= nl2br($result['error']) ?></div><?	
			}
			echo "</li>\n";	
		}
		</ul>*/
		?>
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
			echo "\n--------\n\n";
		}
	}	
	if ($failures) { echo "\n"; }
	echo ($failures == 0) ? "\033[0;37;43m" : "\033[0;37;41m";
	echo ($total_tests-$failures) . '/' . $total_tests . " TESTS SUCCEEDED\033[0m\n";
	if ($failures) { echo "\n"; }
}      
