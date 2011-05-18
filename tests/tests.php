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
$filter_pattern    = NULL;
$format            = 'shell';
$db_name           = 'flourish';
$key_value_string  = '';
$debug             = FALSE;
		
if (!empty($_SERVER['argc'])) {
	foreach (array_slice($_SERVER['argv'], 1) as $arg) {
		// Numeric params are the revision to test
		if (is_numeric($arg)) {
			$revision = $arg;
		
		// Turn on debugging
		} elseif ($arg == '@d') {
			$debug = TRUE;
			
		// Params that start with : filter the configs for a class
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
		
		// Params that start with % are a test name pattern
		} elseif ($arg[0] == '%') {
			$filter_pattern = substr($arg, 1);
			
		// Params that start with = are key=value pairs
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

$ini_path = ' -n ';
if ($debug) {
	$xdebug_path = ini_get('extension_dir') . DIRECTORY_SEPARATOR . ($windows ? 'php_xdebug.dll' : 'xdebug.so');
	if (file_exists($xdebug_path)) {
		$debug_exts = array();
		$debug_exts[] = 'zend_extension="' . $xdebug_path . '"';
		$debug_exts[] = 'xdebug.profiler_enable="1"';
		$debug_exts[] = 'xdebug.profiler_output_dir="' . dirname(__FILE__) .'"';
		$debug_exts[] = 'xdebug.profiler_output_name="cachegrind.out.%p"';
		file_put_contents('./php.ini', join("\n", $debug_exts));
		$ini_path = ' -c ./php.ini ';
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

$master_start = microtime(TRUE);

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
						$configs[$name] = "$phpbin $ini_path -d user_dir=\"DB_NAME:$db_name$key_value_string\" $defines $defines2 $phpunit " . ($bootstrap ? ' --bootstrap ' . escapeshellarg($bootstrap) : '');
					} else {
						throw new Exception();	
					}
				} catch (Exception $e) {
					$defines = make_defines($exts, $lazy_load_exts);
					$configs[$name] = "$phpbin $ini_path -d user_dir=\"DB_NAME:$db_name,SKIPPING:1$key_value_string\" $defines $phpunit " . ($bootstrap ? ' --bootstrap ' . escapeshellarg($bootstrap) : '');	
				}
			}	
		} elseif (!$config_regex) {
			$custom_ini_path = ($ini_path == ' -n ') ? '' : $ini_path;
			$configs[''] = "$phpunit $custom_ini_path";
		}
		
		/* ---------------------------------------------------------- */
		/* For each different configuration, run the tests
		/* ---------------------------------------------------------- */
		$php_errors = 0;
		foreach ($configs as $name => $config) {
			
			$xml_flag = version_compare($phpunit_version, '3.4', '<') ? '--log-xml' : '--log-junit';
			$filter   = strlen($filter_pattern) ? '--filter ' . escapeshellarg($filter_pattern) : '';

			if ($format == 'shell') {
				if ($debug) {
					echo "\033[1;37;46m$config --stderr --log-tap output.tap $xml_flag output.xml $filter $test_name $test_file 2> output.phpunit\033[0;40m\n";
				}
				$name_config = $class_dir . ($name ? ': ' . $name : '');
				echo $name_config;
			}
			
			$start_time = microtime(TRUE);
			$output   = trim(`$config --stderr --log-tap output.tap $xml_flag output.xml $filter $test_name $test_file 2> output.phpunit`);
			$run_time = microtime(TRUE) - $start_time;
			
			// Unfortunately the XML output can't indicate if tests are skipped
			if (file_exists('./output.tap')) {
				$result = file_get_contents('./output.tap');
				if (!$debug) {
					unlink('./output.tap');
				}
			}

			if (file_exists('./output.phpunit')) {
				$phpunit_output = file_get_contents('./output.phpunit');
				if (!$debug) {
					unlink('./output.phpunit');
				}
			} else {
				$phpunit_output = '';
			}

			if (file_exists('./output.xml')) {
				$xml = file_get_contents('./output.xml');
				if (!$debug) {
					unlink('./output.xml');
				}
			} else {
				$xml = '';
			}

			if (stripos($phpunit_output, 'Fatal error') !== FALSE || stripos($phpunit_output, 'RuntimeException') !== FALSE || !trim($xml)) {
				echo "\n    " . str_replace("\n", "\n    ", $output) . "\n";
				$php_errors++;
				continue;
			}
			
			// Read the XML file in
			$xml = new SimpleXMLElement($xml, LIBXML_NOCDATA);
			
			// Parse through the XML and grab each test result
			$testcases = array();
			foreach ((array) $xml->testsuite->xpath('//testcase') as $testcase) {
				$testcases[(string) $testcase['name']] = $testcase;
			}
			
			// Match skipped tests
			$num_skipped =  preg_match_all('/^ok (\d+) - # SKIP/im', $result, $matches);
			$total_tests += $num_skipped;
			$skipped     += $num_skipped;
			
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
				echo ($num_passed) ? "\033[1;37;42mP " . $num_passed : "   ";
				echo "\033[0;40m";
				echo str_pad('', 3-strlen($num_passed), ' ', STR_PAD_RIGHT) . ' ';
				echo ($num_failed) ? "\033[1;37;41mF " . $num_failed : "   ";
				echo "\033[0;40m";
				echo str_pad('', 3-strlen($num_failed), ' ', STR_PAD_RIGHT) . ' ';
				echo ($num_skipped) ? "\033[1;37;44mS " . $num_skipped : "   ";
				echo "\033[0;40m";
				echo str_pad('', 3-strlen($num_skipped), ' ', STR_PAD_RIGHT);
				echo " \033[1;37;46m" . number_format($run_time, 2) . "s\033[0;40m";
				echo "\n";

				if ($output) {
					echo "\033[1;30;47m" . str_replace("\n", "\033[0;40m\n\033[1;30;47m", $output) . "\033[0;40m\n\n";
				}
				
			}
		}
	}
}

if ($format == 'json') {
	
	echo '{"passed": ' . $successful . ', "failed": ' . $failures . ', "skipped": ' . $skipped . ', "php_errors": ' . $php_errors . ', "error_messages": [';
	$total_error_messages = 0;
	foreach ($results as $result) {
		if (!$result['success']) {
			if ($result['config_name']) {
				$parts = explode("\n", $result['error'], 2);
				$parts[0] .= ', config ' . $result['config_name'];
				$result['error'] = join("\n", $parts);
			}
			if ($total_error_messages) {
				echo ', ';
			}
			echo '"';
			echo strtr(
				$result['error'],
				array(
					'"'   => '\"', '\\' => '\\\\', '/'  => '\/', "\x8" => '\b',
					"\xC" => '\f', "\n" => '\n',   "\r" => '\r', "\t"  => '\t'
				)
			);
			echo '"';
			$total_error_messages++;
		}
	}
	echo "]}\n";
	
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
		echo "\033[1;37;42m " . $successful . " Passed \033[0m";
	}
	
	if ($failures) {
		echo "\033[1;37;41m " . $failures . " Failed \033[0m";
	}
	
	if ($php_errors) {
		echo "\033[1;37;41m " . $php_errors . " PHP errors \033[0m";
	}	
	if ($skipped) {
		echo "\033[1;37;44m " . $skipped . " Skipped \033[0m";
	}
	echo "\033[1;37;46m";
	$runtime = microtime(TRUE) - $master_start;

	$runtime_hours   = ($runtime / 3600) % 60;
	$runtime_minutes = ($runtime / 60) % 60;
	$runtime_seconds = $runtime % 60;
	if ($runtime < 1 ) {
		$runtime_seconds = number_format($runtime, 1);
	}

	if ($runtime_hours) {
		echo " $runtime_hours hour";
		if ($runtime_hours != 1) {
			echo "s";
		}
	}
	if ($runtime_minutes) {

		echo " $runtime_minutes minute";
		if ($runtime_minutes != 1) {
			echo "s";
		}
	}
	if ($runtime_seconds) {
		echo " $runtime_seconds second";
		if ($runtime_seconds != 1) {
			echo "s";
		}
	}
	echo " \033[0;40m";
	echo "\n";
}