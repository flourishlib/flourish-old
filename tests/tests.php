#!/usr/bin/php
<?php
date_default_timezone_set('America/New_York');

/* ------------------------------------------------------------------ */
/* Find PHP and PHPUnit
/* ------------------------------------------------------------------ */

$phpunit = NULL;
$phpbin  = NULL;
$windows = FALSE;
$cygwin  = FALSE;

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
	$cygwin  = @stripos(`uname -a`, 'cygwin') !== FALSE;
	$phpbin = 'C:\PHP\php.exe'; 		
}

if (!$phpunit) {
	$phpunit = trim(`which phpunit`);
}
if (!$phpbin) {
	$phpbin  = trim(`which php`);
}

$phpunit_version = preg_replace('#[^0-9\.]+#', '', `$phpunit --version`);


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
if (!file_exists($path) && preg_match('#^Loaded Configuration File => ((?!\(none\)).+)$#m', $output, $matches)) {
	$path = $matches[1]; 
}

preg_match('#additional \.ini files parsed => ((?!\(none\))[^\n]+(\n[a-zA-Z0-9\.\-\_\\\\\/\:]+,?[^\n]*)*)#i', $output, $matches);
if (!empty($matches)) {
	$paths = array_map('trim', explode(',', $matches[1]));
} else {
	$paths = array();
}
array_unshift($paths, $path);

$exts = array();

// These are extensions that may conflict with one another such as APC/XCache that should only be loaded if required
$lazy_load_exts = array();

foreach ($paths as $path) {
	if (!file_exists($path)) {
		continue;	
	}
	foreach (file($path) as $line) {
		$line = trim($line);
		$lazy_load = FALSE;
		if ($line && (substr($line, 0, 19) == '#flourish extension' || substr($line, 0, 24) == '#flourish zend_extension')) {
			$lazy_load = TRUE;
			$line = substr($line, 10);
			
		}
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
		if ($lazy_load) {
			$lazy_load_exts[] = $ext;
		}
		$exts[$ext] = '-d ' . $type . '="' . $file . '"';
	}
}

function make_defines($exts, $exts_to_remove, $throw=FALSE) {
	$use_exts = array();
	foreach ($exts as $ext => $define) {
		if (in_array($ext, $exts_to_remove)) { continue; }
		$use_exts[] = $define;	
	}
	
	$disabled_exts_to_check = array_diff($exts_to_remove, array_keys($exts));
	if ($disabled_exts_to_check) {
		foreach ($disabled_exts_to_check as $ext_to_check) {
			if (extension_loaded($ext_to_check) && $throw) {
				throw new Exception('');
			}
		}
	}
	
	$extra = '';
	if (stripos(php_uname('s'), 'windows') !== FALSE) {
		$extra = ' -d SMTP="' . ini_get('SMTP') . '" -d smtp_port="' . ini_get('smtp_port') . '" ';
	}
	return ' -d memory_limit="' . ini_get('memory_limit') . '" -d include_path="' . ini_get('include_path') . '" ' . $extra . ' -d extension_dir="' . ini_get('extension_dir') . '" ' . join(" ", $use_exts);
}


/* ------------------------------------------------------------------ */
/* Determine the revision or class we are testing
/* ------------------------------------------------------------------ */
$revision          = NULL;
$classes           = array();
$classes_to_remove = array();
$config_matches    = array();
$config_excludes   = array();
$format            = 'shell';
$db_name           = 'flourish';
$key_value_string  = '';
		
if (!empty($_SERVER['argc'])) {
	foreach (array_slice($_SERVER['argv'], 1) as $arg) {
		// Numeric params are the revision to test
		if (is_numeric($arg)) {
			$revision = $arg;
		
		// Params that start with - remove a class from the list
		} elseif ($arg[0] == '-') {
			$classes_to_remove[] = substr($arg, 1);	
			
		// Params that start with : filter the configs for a class
		} elseif ($arg[0] == ':') {
			$config_matches[] = str_replace('*', '.*', substr($arg, 1));
		
		// Params that start with ! remove configs for a class
		} elseif ($arg[0] == '!') {
			$config_excludes[] = str_replace('*', '.*', substr($arg, 1));
		
		// Params that start with . are the output format
		} elseif ($arg[0] == '.') {
			$format = substr($arg, 1);
		
		// Params that start with # are the db name
		} elseif ($arg[0] == '#') {
			$db_name = substr($arg, 1);
			
		// Params that start with = are key=value pairs
		} elseif ($arg[0] == '=') {
			$key_value_string .= ',' . substr($arg, 1);
			
		// All other params are classes
		} else {
			$classes[] = $arg;
		}
	}
}

$config_regex = '';
if ($config_matches) {
	$config_regex = '#^(' . join('|', $config_matches) . ')$#i';
}

$config_exclude_regex = '';
if ($config_excludes) {
	$config_exclude_regex = '#^(' . join('|', $config_excludes) . ')$#i';
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
$total_tests = 0;
$successful  = 0;
$failures    = 0;
$skipped     = 0;

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
				try {
					if ($option && $option[0] == '#') { continue; }
					list($name, $os, $required_exts, $disabled_exts, $defines, $bootstrap) = explode(';', trim($option));
					if ($config_regex && !preg_match($config_regex, $name)) {
						continue;
					}
					if ($config_exclude_regex && preg_match($config_exclude_regex, $name)) {
						continue;
					}
					if ($os) {
						if (substr($os, 0, 1) != '!' && stripos(php_uname('s'), $os) === FALSE) {
							throw new Exception();
						} elseif (substr($os, 0, 1) == '!' && stripos(php_uname('s'), substr($os, 1)) !== FALSE) {
							throw new Exception();
						}
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
									$has_all = $has_all && (extension_loaded($one_required_ext) || isset($exts[$required_ext]));
								}	
								$has_ext = $has_ext || $has_all;
							} else {
								$has_ext = $has_ext || extension_loaded($required_ext) || isset($exts[$required_ext]);
							}
						}
					}
					if ($has_ext) {
						$defines2 = make_defines(
							$exts,
							// Add any non-required lazy-load extensions
							// to the list of disabled extensions for this run
							array_merge(
								$disabled_exts,
								array_diff(
									$lazy_load_exts,
									explode('|', $required_exts)
								)
							),
							TRUE
						);
						// Here we hijack the user_dir ini setting to pass data into the test
						$configs[$name] = "$phpbin -n -d user_dir=\"DB_NAME:$db_name$key_value_string\" $defines $defines2 $phpunit " . ($bootstrap ? ' --bootstrap ' . escapeshellarg($bootstrap) : '');
					} else {
						throw new Exception();	
					}
				} catch (Exception $e) {
					$defines = make_defines($exts, $lazy_load_exts);
					$configs[$name] = "$phpbin -n -d user_dir=\"DB_NAME:$db_name,SKIPPING:1$key_value_string\" $defines $phpunit " . ($bootstrap ? ' --bootstrap ' . escapeshellarg($bootstrap) : '');	
				}
			}	
		} elseif (!$config_regex) {
			$configs[''] = "$phpunit";
		}
		
		/* ---------------------------------------------------------- */
		/* For each different configuration, run the tests
		/* ---------------------------------------------------------- */
		$php_errors = 0;
		foreach ($configs as $name => $config) {
			if ($format == 'shell') {
				$name_config = $class_dir . ($name ? ': ' . $name : '');
				echo $name_config;
			}
			
			$xml_flag = version_compare($phpunit_version, '3.4', '<') ? '--log-xml' : '--log-junit';
			//echo "$config --log-tap tap $xml_flag xml $test_name $test_file\n";
			$output = trim(`$config --log-tap tap $xml_flag xml $test_name $test_file 2> errors`);
			
			$errors = file_exists('./errors') && file_get_contents('./errors');
			
			// This fixes my development machine's PHP shutdown crash with PostgreSQL drivers
			if ($errors && trim(file_get_contents('./errors')) == 'Segmentation fault' && file_exists('./xml')) {
				$errors = FALSE;
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
			foreach ((array) $xml->testsuite->xpath('//testcase') as $testcase) {
				$testcases[(string) $testcase['name']] = $testcase;
			}
			
			// Match skipped tests
			$num_skipped = preg_match_all('/^ok (\d+) - # SKIP/im', $result, $matches);
			$total_tests += $num_skipped;
			$skipped += $num_skipped;
			
			// Match all of the result messages
			$num_passed = 0;
			$num_failed = 0;
			preg_match_all('#^(ok|not ok) (\d+) - (Failure: |Error: )?(?:(\w+)\((\w+)\)|(\w+::\w+))(?:(( with data set \#\d+) [^\n]*)?|\s*)$#ims', $result, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$result = array();
				$result['success'] = ($match[1] == 'ok') ? TRUE : FALSE;
				
				if (!empty($match[6])) {
					list($match[5], $match[4]) = explode('::', $match[6]);
				}
				
				$result['name'] = $match[5] . '::' . $match[4] . "()" . (isset($match[7]) ? $match[7] : '');
				
				// If there was an error, grab the message
				if ($match[1] != 'ok') {
					$testcase_idx = $match[2]-1;
					$key = $match[4] . (isset($match[8]) ? $match[8] : '');
					$testcase = $testcases[$key];
					
					if ($match[3] == 'Failure: ') {
						list($error_message) = (array) $testcase->failure;
					} else {
						list($error_message) = (array) $testcase->error;
					}
					
					$result['config_name'] = $name;
					$result['error'] = trim($error_message);
					$failures++;
					$num_failed++;
				} else {
					$successful++;
					$num_passed++;
				}
				
				$results[] = $result;
				$total_tests++;
			}
			
			if ($format == 'shell') {
				$width = 80;
				$pad_to = $width - (3*6) + 1;
				echo str_pad('', $pad_to - strlen($name_config), ' ');
				echo ($num_passed) ? "\033[1;37;43mP " . $num_passed : "   ";
				echo "\033[0m";
				echo str_pad('', 3-strlen($num_passed), ' ', STR_PAD_RIGHT) . ' ';
				echo ($num_failed) ? "\033[1;37;41mF " . $num_failed : "   ";
				echo "\033[0m";
				echo str_pad('', 3-strlen($num_failed), ' ', STR_PAD_RIGHT) . ' ';
				echo ($num_skipped) ? "\033[1;37;40mS " . $num_skipped : "   ";
				echo "\033[0m";
				echo str_pad('', 3-strlen($num_skipped), ' ', STR_PAD_RIGHT);
				echo "\n";	
			}
		}
	}
}

if ($format == 'json') {
	
	echo '{"passed": ' . $successful . ', "failed": ' . $failures . ', "skipped": ' . $skipped . ', "php_errors": ' . $php_errors . "}\n";
	
} elseif ($format == 'text') {
	
	echo 'passed=' . $successful . "\n";
	echo 'failed=' . $failures . "\n";
	echo 'skipped=' . $skipped . "\n";
	echo 'php_errors=' . $php_errors . "\n";
	
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
	
	if ($successful) {
		echo "\033[1;37;43m " . $successful . " Passed \033[0m";
		if ($failures || $php_errors || $skipped) {
			//echo ", ";
		}	
	}
	
	if ($failures) {
		echo "\033[1;37;41m " . $failures . " Failed \033[0m";
		if ($php_errors || $skipped) {
			//echo ", ";
		}	
	}
	
	if ($php_errors) {
		echo "\033[1;37;41m " . $php_errors . " PHP errors \033[0m";
		if ($skipped) {
			//echo ", ";
		}	
	}	
	if ($skipped) {
		echo "\033[0;37;40m " . $skipped . " Skipped \033[0m";
	}
	echo "\n";
}
