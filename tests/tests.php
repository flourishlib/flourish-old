#!/usr/bin/php
<?php

/* ------------------------------------------------------------------ */
/* Find PHP and PHPUnit
/* ------------------------------------------------------------------ */

$phpunit = NULL;
$phpbin  = NULL;
$windows = FALSE;

// This should be made more robust at some point
if (file_exists('/usr/bin/phpunit') && is_executable('/usr/bin/phpunit')) {
	$phpunit = '/usr/bin/phpunit'; 		
}
if (file_exists('/usr/bin/php') && is_executable('/usr/bin/php')) {
	$phpbin = '/usr/bin/php'; 		
}

if (!$phpunit && file_exists('C:\PHP\phpunit')) {
	$phpunit = 'C:\PHP\phpunit'; 		
}
if (!$phpbin && file_exists('C:\PHP\php.exe')) {
	$windows = TRUE;
	$phpbin = 'C:\PHP\php.exe'; 		
}

if (!$phpunit) {
	$phpunit = trim(`which phpunit`);
}
if (!$phpbin) {
	$phpbin  = trim(`which php`);
}


/* ------------------------------------------------------------------ */
/* Find dynmically loaded extensions
/* ------------------------------------------------------------------ */
$params = ($windows) ? ' -c "C:\PHP\\php.ini" ' : '';
$output = `$phpbin $params -r "phpinfo(INFO_GENERAL);"`;
preg_match('#^Configuration File \(php.ini\) Path => (.*)$#m', $output, $matches);
$path = $matches[1];
if (is_dir($path)) {
	$path .= DIRECTORY_SEPARATOR . 'php.ini';	
}
if (!file_exists($path) && preg_match('#^Loaded Configuration File => (.+)$#m', $output, $matches)) {
	$path = $matches[1]; 		
}

preg_match('#additional \.ini files parsed => ((?!\(none\))[^\n]+(\n[a-zA-Z0-9\.\-\_\\\\\/\:]+,?[^\n]*)*)#', $output, $matches);
if (!empty($matches)) {
	$paths = array_map('trim', explode(',', $matches[1]));
} else {
	$paths = array();	
}
array_unshift($paths, $path);

$exts = array();
foreach ($paths as $path) {
	foreach (file($path) as $line) {
		$line = trim($line);
		if (!$line || $line[0] == '[' || $line[0] == ';' || $line[0] == '#') {
			continue;
		}	
		if (strtolower(substr($line, 0, 9)) != 'extension' && strtolower(substr($line, 0, 14)) != 'zend_extension') {
			continue;	
		}
		if (stripos($line, 'zend_extension_manager') !== FALSE || stripos($line, 'ZendExtensionManager') !== FALSE) {
			continue;	
		}
		if (stripos($line, 'extension_dir') === 0) {
			continue;
		}
		$line = trim(preg_replace('/[;#].*$/m', '', $line));
		list($type,$file) = array_map('trim', explode('=', $line));
		if (preg_match('#^(\'|").*(\'|")$#', $file)) {
			$file = substr($file, 1, -1);	
		}
		preg_match('#(?:php_)?([a-zA-Z0-9\_\-\.]+)\.\w+$#', $file, $match);
		$ext = $match[1];
		if ($ext == 'gd2') {
			$ext = 'gd';	
		}
		if ($ext == 'sqlsrv_ts') {
			$ext = 'sqlsrv';	
		}
		$exts[$ext] = '-d ' . $type . '="' . $file . '"';
	}
}

function make_defines($exts, $exts_to_remove) {
	$use_exts = array();
	foreach ($exts as $ext => $define) {
		if (in_array($ext, $exts_to_remove)) { continue; }
		$use_exts[] = $define;	
	}
	return ' -d memory_limit="' . ini_get('memory_limit') . '" -d include_path="' . ini_get('include_path') . '" -d extension_dir="' . ini_get('extension_dir') . '" ' . join(" ", $use_exts);
}


/* ------------------------------------------------------------------ */
/* Determine the revision or class we are testing
/* ------------------------------------------------------------------ */
$revision = NULL;
$classes  = array();
$classes_to_remove = array();
$config_matches = array();
		
if (!empty($_SERVER['argc'])) {
	if (sizeof($_SERVER['argv']) == 2 && is_numeric($_SERVER['argv'][1])) {
		$revision = $_SERVER['argv'][1];	
	} else {
		foreach (array_slice($_SERVER['argv'], 1) as $class) {
			if ($class[0] == '-') {
				$classes_to_remove[] = substr($class, 1);	
			} elseif ($class[0] == ':') {
				$config_matches[] = str_replace('*', '.*', substr($class, 1));
			} else {
				$classes[] = $class;	
			}
		} 		
	}
}

$config_regex = '';
if ($config_matches) {
	$config_regex = '#' . join('|', $config_matches) . '#i';
}


/* ------------------------------------------------------------------ */
/* Get the class directories to run tests for
/* ------------------------------------------------------------------ */
$class_root = './classes/';
if ($classes) {
	$class_dirs = array_diff($classes, $classes_to_remove);
} else {
	$class_dirs = array_diff(scandir($class_root), array('.', '..', '.svn'));
	$class_dirs = array_diff($class_dirs, $classes_to_remove);
}

$results     = array();
$failures    = 0;
$total_tests = 0;


/* ------------------------------------------------------------------ */
/* Run through each class dir looking for test classes
/* ------------------------------------------------------------------ */
foreach ($class_dirs as $class_dir) {
	$class_path  = $class_root . $class_dir;
	$class_tests = array_diff(scandir($class_path), array('.', '..', '.svn'));
	
	
	/* -------------------------------------------------------------- */
	/* Run each test class
	/* -------------------------------------------------------------- */
	foreach ($class_tests as $class_test) {
		
		// Ignore anything that isn't a PHP script with Test in the filename
		// this allows us to ignore .configs and other supporting files
		if (!preg_match('#^.*Test.*\.php$#', $class_test)) {
			continue;	
		}
		
		$test_name = preg_replace('#\.php$#i', '', $class_test);
		$test_file = $class_path . DIRECTORY_SEPARATOR . $class_test;

		
		// Look for a .configs file so we can test different configurations
		$configs = array();
		if (file_exists($class_path . DIRECTORY_SEPARATOR . $test_name . '.configs')) {
			$options = file($class_path . DIRECTORY_SEPARATOR . $test_name . '.configs');
			foreach ($options as $option) {
				if ($option && $option[0] == '#') { continue; }
				list($name, $os, $required_exts, $disabled_exts, $defines, $bootstrap) = explode(';', trim($option));
				if ($config_regex && !preg_match($config_regex, $name)) {
					continue;	
				}
				if ($os && stripos(php_uname('s'), $os) === FALSE) {
					continue;
				}
				if ($windows) {
					$disabled_exts = str_replace('pdo_dblib', 'pdo_mssql', $disabled_exts);
					$required_exts = str_replace('pdo_dblib', 'pdo_mssql', $required_exts);
				}
				$disabled_exts = explode(',', $disabled_exts);
				$has_ext = !$required_exts;
				if ($required_exts) {
					foreach (explode('|', $required_exts) as $required_ext) {
						if (strpos($required_ext, '&') !== FALSE) {
							$has_all = TRUE;
							foreach (explode('&', $required_ext) as $one_required_ext) {
								$has_all = $has_all && extension_loaded($one_required_ext);
							}	
							$has_ext = $has_ext || $has_all;
						} else {
							$has_ext = $has_ext || extension_loaded($required_ext);
						}
					}
				}
				if ($has_ext) {
					$defines2 = make_defines($exts, $disabled_exts);
					$configs[$name] = "$phpbin -n $defines $defines2 $phpunit " . ($bootstrap ? ' --bootstrap ' . escapeshellarg($bootstrap) : '') ;
				}
			}	
		} else {
			$configs[''] = "$phpunit";	
		}
		
		/* ---------------------------------------------------------- */
		/* For each different configuration, run the tests
		/* ---------------------------------------------------------- */
		$php_errors = 0;
		foreach ($configs as $name => $config) {
			if (!$revision) {
				echo $class_dir . ($name ? ': ' . $name : '') . "\n";
			}
			
			//echo "$config --log-tap ./tap --log-xml ./xml $test_name $test_file\n";
			$output = trim(`$config --log-tap tap --log-xml xml $test_name $test_file 2> errors`);	
			
			//echo $output;
			
			$errors = file_exists('./errors') && file_get_contents('./errors');
			
			// This fixes my development machine's PHP shutdown crash with PostgreSQL drivers
			if ($errors && trim(file_get_contents('./errors')) == 'Segmentation fault' && file_exists('./xml')) {
				$errors = FALSE;
			}
			
			if (preg_match_all('#<pre class="exposed">(.*?)</pre>#ims', $output, $matches)) {
				foreach ($matches[1] as $match) {
					echo html_entity_decode($match, ENT_COMPAT, 'utf-8') . "\n";	
				}
			}
			
			if (!$revision && (stripos($output, 'Fatal error') !== FALSE || stripos($output, 'RuntimeException') !== FALSE || $errors || !file_exists('./xml') || !trim(file_get_contents('./xml')))) {
				echo $output . "\n";
				if (file_exists('./xml')) {
					unlink('./xml');	
				}
				if ($errors) {
					echo file_get_contents('./errors');	
				}
				unlink('./errors');
				if ($windows) {
					echo "\nPHP ERROR\n";
				} else {
					echo "\n\033[0;37;41mPHP ERROR\033[0m\n";
				}
				if (file_exists('./tap')) {
					unlink('./tap');
				}
				$php_errors++;
				continue;		
			}
			
			// Read the XML file in
			$xml = new SimpleXMLElement(file_get_contents('./xml'), LIBXML_NOCDATA);
			unlink('./xml');
			
			$result = file_get_contents('./tap');
			unlink('./tap');

			unlink('./errors');
			
			// Remove some output we don't care about
			$result = preg_replace('#^PHPUnit.*?$\s+\d+\.\.\d+\s+#ims', '', $result);
			$result = preg_replace('/\s*^# TestSuite "\w+" (ended|started)\.\s*$\s*/ims', "", $result);
			
			// Parse through the XML and grab each test result
			$testcases = array();
			foreach ((array) $xml->testsuite->children() as $key => $value) {
				if ($key == '@attributes') { continue; }
				
				if (!is_array($value)) {
					$value = array($value);	
				}
				
				if ($key == 'testsuite') {
					foreach ($value as $testsuite) {
						foreach ((array) $testsuite->children() as $key2 => $testcase_array) {
							if ($key2 == '@attributes') { continue; }
							foreach ($testcase_array as $testcase) {
								$attributes = (array) $testcase->attributes();
								$attributes = $attributes['@attributes'];
								$testcases[$attributes['name']] = $testcase;
							}	
						}	
					}
				} else {
					foreach ($value as $testcase) {
						$attributes = (array) $testcase->attributes();
						$attributes = $attributes['@attributes'];
						$testcases[$attributes['name']] = $testcase;
					}	
				}
			}
			
			// Match all of the result messages
			preg_match_all('#^(ok|not ok) (\d+) - (Failure: |Error: )?(\w+)\((\w+)\)(?:(( with data set \#\d+) [^\n]*)?|\s*)$#ims', $result, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$result = array();
				$result['success'] = ($match[1] == 'ok') ? TRUE : FALSE;
				$result['name'] = $match[5] . '::' . $match[4] . "()" . (isset($match[6]) ? $match[6] : '');
				
				// If there was an error, grab the message
				if ($match[1] != 'ok') {
					$testcase_idx = $match[2]-1;
					$key = $match[4] . (isset($match[7]) ? $match[7] : '');
					$testcase = $testcases[$key];
					
					if ($match[3] == 'Failure: ') {
						list($error_message) = (array) $testcase->failure;
					} else {
						list($error_message) = (array) $testcase->error;
					}
					
					$result['config_name'] = $name;
					$result['error'] = trim($error_message);
					$failures++;
				}
				
				$results[] = $result;
				$total_tests++;
			}
		}
	}
}


// If a revision is passed the output is for the unit test
// page on the site, so let's make some HTML
if ($revision) {
	
	?>
	<div class="revision <?php echo ($failures == 0) ? 'success' : 'failure'  ?>">
		<h2 class="revision_number">Revision <?php echo $revision ?></h2>
		<div class="successful_tests">
			<span class="tally"><?php echo $total_tests-$failures ?>/<?php echo $total_tests ?></span>
			<span class="description">tests succeeded</span>
		</div>
		<a class="download_link" href="/download/tests/flourish_tests_r<?php echo $revision ?>.zip">Download Unit Tests</a>
	</div>
	<?php	
	
// If the script was called without a revision number, this is probably
// being used during developement, so we format for terminal output
} else {
	
	if ($failures) { echo "\n"; }
	foreach ($results as $result) {
		if (!$result['success']) {
			echo "FAILURE in " . $result['config_name'] . " " . $result['name'] . "\n\n";
			$lines = explode("\n", $result['error']);
			foreach ($lines as $line) {
				echo "  " . $line . "\n";	
			}
			echo "\n--------\n\n";
		}
	}	
	
	if (!$windows) {
		echo ($failures == 0 && !$php_errors) ? "\033[0;37;43m" : "\033[0;37;41m";
	}
	echo ($total_tests-$failures) . '/' . $total_tests . " TESTS SUCCEEDED";
	if ($php_errors) {
		echo ", " . $php_errors . " PHP " . ($php_errors == 1 ? 'ERROR' : 'ERRORS');		
	}
	if (!$windows) {
		echo "\033[0m";
	}
	echo "\n";
}      
