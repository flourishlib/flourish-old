<?php
/**
 * Provides file manipulation functionality for {@link fActiveRecord} classes
 * 
 * @copyright  Copyright (c) 2008 William Bond
 * @author     William Bond [wb] <will@flourishlib.com>
 * @license    http://flourishlib.com/license
 * 
 * @package    Flourish
 * @link       http://flourishlib.com/fORMFile
 * 
 * @version    1.0.0b
 * @changes    1.0.0b  The initial implementation [wb, 2008-05-28]
 */
class fORMFile
{
	/**
	 * The temporary directory to use for various tasks
	 * 
	 * @internal
	 * 
	 * @var string
	 */
	const TEMP_DIRECTORY = '__flourish_temp/';
	
	
	/**
	 * Defines how columns can inherit uploaded files
	 * 
	 * @var array
	 */
	static private $column_inheritence = array();
	
	/**
	 * Methods to be called on fUpload before the file is uploaded
	 * 
	 * @var array
	 */
	static private $fupload_method_calls = array();
	
	/**
	 * Columns that can be filled by file uploads
	 * 
	 * @var array
	 */
	static private $file_upload_columns = array();
	
	/**
	 * Methods to be called on the fImage instance
	 * 
	 * @var array
	 */
	static private $fimage_method_calls = array();
	
	/**
	 * Columns that can be filled by image uploads
	 * 
	 * @var array
	 */
	static private $image_upload_columns = array();
	
	/**
	 * Keeps track of the nesting level of the filesystem transaction so we know when to start, commit, rollback, etc
	 * 
	 * @var integer
	 */
	static private $transaction_level = 0;
	
	
	/**
	 * Adds an {@link fImage} method call to the image manipulation for a column if an image file is uploaded
	 * 
	 * @param  mixed  $class       The class name or instance of the class
	 * @param  string $column      The column to call the method for
	 * @param  string $method      The fImage method to call
	 * @param  array  $parameters  The parameters to pass to the method
	 * @return void
	 */
	static public function addFImageMethodCall($class, $column, $method, $parameters=array())
	{
		$class = fORM::getClass($class);
		
		if (!array_key_exists($column, self::$image_upload_columns[$class])) {
			fCore::toss(
				'fProgrammerException',
				fGrammar::compose(
					'The column specified, %s, has not been configured as an image upload column.',
					fCore::dump($column)
				)
			);
		}
		
		if (empty(self::$fimage_method_calls[$class])) {
			self::$fimage_method_calls[$class] = array();
		}
		if (empty(self::$fimage_method_calls[$class][$column])) {
			self::$fimage_method_calls[$class][$column] = array();
		}
		
		self::$fimage_method_calls[$class][$column][] = array(
			'method'     => $method,
			'parameters' => $parameters
		);
	}
	
	
	/**
	 * Adds an {@link fUpload} method call to the {@link fUpload} initialization for a column
	 * 
	 * @param  mixed  $class       The class name or instance of the class
	 * @param  string $column      The column to call the method for
	 * @param  string $method      The fUpload method to call
	 * @param  array  $parameters  The parameters to pass to the method
	 * @return void
	 */
	static public function addFUploadMethodCall($class, $column, $method, $parameters=array())
	{
		$class = fORM::getClass($class);
		
		if (empty(self::$file_upload_columns[$class][$column])) {
			fCore::toss(
				'fProgrammerException',
				fGrammar::compose(
					'The column specified, %s, has not been configured as a file or image upload column.',
					fCore::dump($column)
				)
			);
		}
		
		if (empty(self::$fupload_method_calls[$class])) {
			self::$fupload_method_calls[$class] = array();
		}
		if (empty(self::$fupload_method_calls[$class][$column])) {
			self::$fupload_method_calls[$class][$column] = array();
		}
		
		self::$fupload_method_calls[$class][$column][] = array(
			'callback'   => array('fUpload', $method),
			'parameters' => $parameters
		);
	}
	
	
	/**
	 * Begins a transaction, or increases the level
	 * 
	 * @internal
	 * 
	 * @return void
	 */
	static public function begin()
	{
		// If the transaction was started by something else, don't even track it
		if (self::$transaction_level == 0 && fFilesystem::isInsideTransaction()) {
			return;
		}
		
		self::$transaction_level++;
		
		if (!fFilesystem::isInsideTransaction()) {
			fFilesystem::begin();
		}
	}
	
	
	/**
	 * Commits a transaction, or decreases the level
	 * 
	 * @internal
	 * 
	 * @return void
	 */
	static public function commit()
	{
		// If the transaction was started by something else, don't even track it
		if (self::$transaction_level == 0) {
			return;
		}
		
		self::$transaction_level--;
		
		if (!self::$transaction_level) {
			fFilesystem::commit();
		}
	}
	
	
	/**
	 * Sets a column to be a file upload column
	 * 
	 * @param  mixed             $class      The class name or instance of the class
	 * @param  string            $column     The column to set as a file upload column
	 * @param  fDirectory|string $directory  The directory to upload to
	 * @return void
	 */
	static public function configureFileUploadColumn($class, $column, $directory)
	{
		$class     = fORM::getClass($class);
		$table     = fORM::tablize($class);
		$data_type = fORMSchema::getInstance()->getColumnInfo($table, $column, 'type');
		
		$valid_data_types = array('varchar', 'char', 'text');
		if (!in_array($data_type, $valid_data_types)) {
			fCore::toss(
				'fProgrammerException',
				fGrammar::compose(
					'The column specified, %1$s, is a %2$s column. Must be one of %3$s to be set as a file upload column.',
					fCore::dump($column),
					$data_type,
					join(', ', $valid_data_types)
				)
			);
		}
		
		if (!is_object($directory)) {
			$directory = new fDirectory($directory);
		}
		
		if (!$directory->isWritable()) {
			fCore::toss(
				'fEnvironmentException',
				fGrammar::compose(
					'The file upload directory, %s, is not writable',
					$directory->getPath()
				)
			);
		}
		
		$camelized_column = fGrammar::camelize($column, TRUE);
		
		fORM::registerHookCallback(
			$class,
			'replace::inspect' . $camelized_column . '()',
			array('fORMFile', 'inspect')
		);
		
		fORM::registerHookCallback(
			$class,
			'replace::upload' . $camelized_column . '()',
			array('fORMFile', 'upload')
		);
		
		fORM::registerHookCallback(
			$class,
			'replace::set' . $camelized_column . '()',
			array('fORMFile', 'set')
		);
		
		fORM::registerHookCallback(
			$class,
			'replace::encode' . $camelized_column . '()',
			array('fORMFile', 'encode')
		);
		
		fORM::registerHookCallback(
			$class,
			'replace::prepare' . $camelized_column . '()',
			array('fORMFile', 'prepare')
		);
		
		fORM::registerReflectCallback(
			$class,
			array('fORMFile', 'reflect')
		);
		
		fORM::registerObjectifyCallback(
			$class,
			$column,
			array('fORMFile', 'objectify')
		);
		
		$only_once_hooks = array(
			'post-begin::delete()'    => array('fORMFile', 'begin'),
			'pre-commit::delete()'    => array('fORMFile', 'delete'),
			'post-commit::delete()'   => array('fORMFile', 'commit'),
			'post-rollback::delete()' => array('fORMFile', 'rollback'),
			'post::populate()'        => array('fORMFile', 'populate'),
			'post-begin::store()'     => array('fORMFile', 'begin'),
			'post-validate::store()'  => array('fORMFile', 'moveFromTemp'),
			'pre-commit::store()'     => array('fORMFile', 'deleteOld'),
			'post-commit::store()'    => array('fORMFile', 'commit'),
			'post-rollback::store()'  => array('fORMFile', 'rollback'),
			'post::validate()'        => array('fORMFile', 'validate')
		);
		
		foreach ($only_once_hooks as $hook => $callback) {
			if (!fORM::checkHookCallback($class, $hook, $callback)) {
				fORM::registerHookCallback($class, $hook, $callback);
			}
		}
		
		if (empty(self::$file_upload_columns[$class])) {
			self::$file_upload_columns[$class] = array();
		}
		
		self::$file_upload_columns[$class][$column] = $directory;
	}
	
	
	/**
	 * Takes one file or image upload columns and sets it to inherit any uploaded files from another column
	 * 
	 * @param  mixed  $class                The class name or instance of the class
	 * @param  string $column               The column that will inherit the uploaded file
	 * @param  string $inherit_from_column  The column to inherit the uploaded file from
	 * @return void
	 */
	static public function configureColumnInheritance($class, $column, $inherit_from_column)
	{
		$class = fORM::getClass($class);
		
		if (empty(self::$column_inheritence[$class])) {
			self::$column_inheritence[$class] = array();
		}
		
		if (empty(self::$column_inheritence[$class][$inherit_from_column])) {
			self::$column_inheritence[$class][$inherit_from_column] = array();
		}
		
		self::$column_inheritence[$class][$inherit_from_column][] = $column;
	}
	
	
	/**
	 * Sets a column to be a date created column
	 * 
	 * @param  mixed             $class       The class name or instance of the class
	 * @param  string            $column      The column to set as a file upload column
	 * @param  fDirectory|string $directory   The directory to upload to
	 * @param  string            $image_type  The image type to save the image as. Valid: {null}, 'gif', 'jpg', 'png'
	 * @return void
	 */
	static public function configureImageUploadColumn($class, $column, $directory, $image_type=NULL)
	{
		$valid_image_types = array(NULL, 'gif', 'jpg', 'png');
		if (!in_array($image_type, $valid_image_types)) {
			$valid_image_types[0] = '{null}';
			fCore::toss(
				'fProgrammerException',
				fGrammar::compose(
					'The image type specified, %1$s, is not valid. Must be one of: %2$s.',
					fCore::dump($image_type),
					join(', ', $valid_image_types)
				)
			);
		}
		
		self::configureFileUploadColumn($class, $column, $directory);
		
		$class = fORM::getClass($class);
		
		if (empty(self::$image_upload_columns[$class])) {
			self::$image_upload_columns[$class] = array();
		}
		
		self::$image_upload_columns[$class][$column] = $image_type;
		
		self::addFUploadMethodCall($class, $column, 'setType', array('image'));
	}
	
	
	/**
	 * Deletes the files for this record
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object            The fActiveRecord instance
	 * @param  array         &$values           The current values
	 * @param  array         &$old_values       The old values
	 * @param  array         &$related_records  Any records related to this record
	 * @return void
	 */
	static public function delete($object, &$values, &$old_values, &$related_records)
	{
		$class = get_class($object);
		
		foreach (self::$file_upload_columns[$class] as $column => $directory) {
			
			// Remove the current file for the column
			if ($values[$column] instanceof fFile) {
				$values[$column]->delete();
			}
			
			// Remove the old files for the column
			if (isset($old_values[$column])) {
				foreach ($old_values[$column] as $file) {
					if ($file instanceof fFile) {
						$file->delete();
					}
				}
			}
			
		}
	}
	
	
	/**
	 * Deletes old files for this record that have been replaced
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object            The fActiveRecord instance
	 * @param  array         &$values           The current values
	 * @param  array         &$old_values       The old values
	 * @param  array         &$related_records  Any records related to this record
	 * @return void
	 */
	static public function deleteOld($object, &$values, &$old_values, &$related_records)
	{
		$class = get_class($object);
		
		foreach (self::$file_upload_columns[$class] as $column => $directory) {
			
			// Remove the old files for the column
			if (isset($old_values[$column])) {
				foreach ($old_values[$column] as $file) {
					if ($file instanceof fFile) {
						$file->delete();
					}
				}
			}
			
		}
	}
	
	
	/**
	 * Encodes a file for output into an HTML input tag
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object            The fActiveRecord instance
	 * @param  array         &$values           The current values
	 * @param  array         &$old_values       The old values
	 * @param  array         &$related_records  Any records related to this record
	 * @param  string        &$method_name      The method that was called
	 * @param  array         &$parameters       The parameters passed to the method
	 * @return void
	 */
	static public function encode($object, &$values, &$old_values, &$related_records, &$method_name, &$parameters)
	{
		list ($action, $column) = fORM::parseMethod($method_name);
		
		$filename = ($values[$column] instanceof fFile) ? $values[$column]->getFilename() : NULL;
		if ($filename && strpos($values[$column]->getPath(), self::TEMP_DIRECTORY . $filename) !== FALSE) {
			$filename = self::TEMP_DIRECTORY . $filename;
		}
		
		return fHTML::encode($filename);
	}
	
	
	/**
	 * Returns the metadata about a column including features added by this class
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object            The fActiveRecord instance
	 * @param  array         &$values           The current values
	 * @param  array         &$old_values       The old values
	 * @param  array         &$related_records  Any records related to this record
	 * @param  string        &$method_name      The method that was called
	 * @param  array         &$parameters       The parameters passed to the method
	 * @return mixed  The metadata array or element specified
	 */
	static public function inspect($object, &$values, &$old_values, &$related_records, &$method_name, &$parameters)
	{
		list ($action, $column) = fORM::parseMethod($method_name);
		
		$class   = get_class($object);
		$info    = fORMSchema::getInstance()->getColumnInfo(fORM::tablize($class), $column);
		$element = (isset($parameters[0])) ? $parameters[0] : NULL;
		
		if (!empty(self::$image_upload_columns[$class][$column])) {
			$info['feature'] = 'image';
			
		} elseif (!empty(self::$file_upload_columns[$class][$column])) {
			$info['feature'] = 'file';
		}
		
		$info['directory'] = self::$file_upload_columns[$class][$column]->getPath();
		
		if ($element) {
			return (isset($info[$element])) ? $info[$element] : NULL;
		}
		
		return $info;
	}
	
	
	/**
	 * Moves uploaded file from the temporary directory to the permanent directory
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object            The fActiveRecord instance
	 * @param  array         &$values           The current values
	 * @param  array         &$old_values       The old values
	 * @param  array         &$related_records  Any records related to this record
	 * @return void
	 */
	static public function moveFromTemp($object, &$values, &$old_values, &$related_records)
	{
		foreach ($values as $column => $value) {
			if (!$value instanceof fFile) {
				continue;
			}
			
			// If the file is in a temp dir, move it out
			if (stripos($value->getDirectory()->getPath(), self::TEMP_DIRECTORY) !== FALSE) {
				$new_filename = str_replace(self::TEMP_DIRECTORY, '', $value->getPath());
				$new_filename = fFilesystem::createUniqueName($new_filename);
				$value->rename($new_filename, FALSE);
			}
		}
	}
	
	
	/**
	 * Turns a filename into an {@link fFile} or {@link fImage} object
	 * 
	 * @internal
	 * 
	 * @param  string $class   The class this value is for
	 * @param  string $column  The column the value is in
	 * @param  mixed  $value   The value
	 * @return mixed  The {@link fFile}, {@link fImage} or raw value
	 */
	static public function objectify($class, $column, $value)
	{
		if (!fCore::stringlike($value)) {
			return $value;
		}
		
		$path = self::$file_upload_columns[$class][$column]->getPath() . $value;
		
		try {
			
			if (fImage::isImageCompatible($path)) {
				return new fImage($path);
			}
			
			return new fFile($path);
			 
		// If there was some error creating the file, just return the raw value
		} catch (fExpectedException $e) {
			return $value;
		}
	}
	
	
	/**
	 * Performs the upload action for file uploads during {@link fActiveRecord::populate()}
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object            The fActiveRecord instance
	 * @param  array         &$values           The current values
	 * @param  array         &$old_values       The old values
	 * @param  array         &$related_records  Any records related to this record
	 * @return void
	 */
	static public function populate($object, &$values, &$old_values, &$related_records)
	{
		$class = get_class($object);
		
		foreach (self::$file_upload_columns[$class] as $column => $directory) {
			if (fUpload::check($column)) {
				$method = 'upload' . fGrammar::camelize($column, TRUE);
				$object->$method();
			}
		}
	}
	
	
	/**
	 * Prepares a file for output into HTML by returning the web server path to the file
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object            The fActiveRecord instance
	 * @param  array         &$values           The current values
	 * @param  array         &$old_values       The old values
	 * @param  array         &$related_records  Any records related to this record
	 * @param  string        &$method_name      The method that was called
	 * @param  array         &$parameters       The parameters passed to the method
	 * @return void
	 */
	static public function prepare($object, &$values, &$old_values, &$related_records, &$method_name, &$parameters)
	{
		list ($action, $column) = fORM::parseMethod($method_name);
		
		if (sizeof($parameters) > 1) {
			fCore::toss(
				'fProgrammerException',
				fGrammar::compose(
					'The column specified, %s, does not accept more than one parameter',
					fCore::dump($column)
				)
			);
		}
		
		$translate_to_web_path = (empty($parameters[0])) ? FALSE : TRUE;
		$value                 = $values[$column];
		
		if ($value instanceof fFile) {
			$path = ($translate_to_web_path) ? $value->getPath(TRUE) : $value->getFilename();
		} else {
			$path = NULL;
		}
		
		return fHTML::prepare($path);
	}
	
	
	/**
	 * Performs image manipulation on an uploaded image
	 * 
	 * @internal
	 * 
	 * @param  string $class   The name of the class we are manipulating the image for
	 * @param  string $column  The column the image is assigned to
	 * @param  fFile  $image   The image object to manipulate
	 * @return void
	 */
	static public function processImage($class, $column, $image)
	{
		// If we don't have an image or we haven't set it up to manipulate images, just exit
		if (!$image instanceof fImage || empty(self::$fimage_method_calls[$class][$column])) {
			return;
		}
		
		// Manipulate the image
		if (!empty(self::$fimage_method_calls[$class][$column])) {
			foreach (self::$fimage_method_calls[$class][$column] as $method_call) {
				$callback   = array($image, $method_call['method']);
				$parameters = $method_call['parameters'];
				if (!is_callable($callback)) {
					fCore::toss(
						'fProgrammerException',
						fGrammar::compose(
							'The fImage method specified, %s, is not a valid method.',
							fCore::dump($method_call['method']) . '()'
						)
					);
				}
				call_user_func_array($callback, $parameters);
			}
		}
		
		// Save the changes
		$callback   = array($image, 'saveChanges');
		$parameters = array(self::$image_upload_columns[$class][$column]);
		call_user_func_array($callback, $parameters);
	}
	
	
	/**
	 * Adjusts the {@link fActiveRecord::reflect()} signatures of columns that have been configured in this class
	 * 
	 * @internal
	 * 
	 * @param  string  $class                 The class to reflect
	 * @param  array   &$signatures           The associative array of {method name} => {signature}
	 * @param  boolean $include_doc_comments  If doc comments should be included with the signature
	 * @return void
	 */
	static public function reflect($class, &$signatures, $include_doc_comments)
	{
		$image_columns = (isset(self::$image_upload_columns[$class])) ? array_keys(self::$image_upload_columns[$class]) : array();
		$file_columns  = (isset(self::$file_upload_columns[$class]))  ? array_keys(self::$file_upload_columns[$class])  : array();
		
		foreach(self::$link_columns[$class] as $column => $enabled) {
			$signature = '';
			if ($include_doc_comments) {
				$signature .= "/**\n";
				$signature .= " * Prepares the value of " . $column . " for output into HTML\n";
				$signature .= " * \n";
				$signature .= " * This method will ensure all links that start with a domain name are preceeded by http://\n";
				$signature .= " * \n";
				$signature .= " * @return string  The HTML-ready value\n";
				$signature .= " */\n";
			}
			$prepare_method = 'prepare' . fGrammar::camelize($column, TRUE);
			$signature .= 'public function prepare' . $prepare_method . '()';
			
			$signatures[$prepare_method] = $signature;
		}
		
		foreach($file_columns as $column) {
			$camelized_column = fGrammar::camelize($column, TRUE);
			
			$noun = 'file';
			if (in_array($column, $image_columns)) {
				$noun = 'image';
			}
			
			$signature = '';
			if ($include_doc_comments) {
				$signature .= "/**\n";
				$signature .= " * Encodes the filename of " . $column . " for output into an HTML form\n";
				$signature .= " * \n";
				$signature .= " * Only the filename will be returned, any directory will be stripped.\n";
				$signature .= " * \n";
				$signature .= " * @return string  The HTML form-ready value\n";
				$signature .= " */\n";
			}
			$encode_method = 'encode' . $camelized_column;
			$signature .= 'public function ' . $encode_method . '()';
			
			$signatures[$encode_method] = $signature;
			
			$signature = '';
			if ($include_doc_comments) {
				$signature .= "/**\n";
				$signature .= " * Prepares the filename of " . $column . " for output into HTML\n";
				$signature .= " * \n";
				$signature .= " * By default only the filename will be returned and any directory will be stripped.\n";
				$signature .= " * The \$include_web_path parameter changes this behaviour.\n";
				$signature .= " * \n";
				$signature .= " * @param  boolean \$include_web_path  If the full web path to the " . $noun . " should be included\n";
				$signature .= " * @return string  The HTML-ready value\n";
				$signature .= " */\n";
			}
			$prepare_method = 'prepare' . $camelized_column;
			$signature .= 'public function ' . $prepare_method . '($include_web_path=FALSE)';
			
			$signatures[$prepare_method] = $signature;
			
			$signature = '';
			if ($include_doc_comments) {
				$signature .= "/**\n";
				$signature .= " * Takes a file uploaded through an HTML form for " . $column . " and moves it into the specified directory\n";
				$signature .= " * \n";
				$signature .= " * Any columns that were designated as inheriting from this column will get a copy\n";
				$signature .= " * of the uploaded file.\n";
				$signature .= " * \n";
				if ($noun == 'image') {
					$signature .= " * Any fImage calls that were added to the column will be processed on the uploaded image.\n";
					$signature .= " * \n";
				}
				$signature .= " * @return void\n";
				$signature .= " */\n";
			}
			$upload_method = 'upload' . $camelized_column;
			$signature .= 'public function ' . $upload_method . '()';
			
			$signatures[$upload_method] = $signature;
			
			$signature = '';
			if ($include_doc_comments) {
				$signature .= "/**\n";
				$signature .= " * Takes a file that exists on the filesystem and copies it into the specified directory for " . $column . "\n";
				$signature .= " * \n";
				if ($noun == 'image') {
					$signature .= " * Any fImage calls that were added to the column will be processed on the copied image.\n";
					$signature .= " * \n";
				}
				$signature .= " * @return void\n";
				$signature .= " */\n";
			}
			$set_method = 'set' . $camelized_column;
			$signature .= 'public function ' . $set_method . '()';
			
			$signatures[$set_method] = $signature;
			
			$signature = '';
			if ($include_doc_comments) {
				$signature .= "/**\n";
				$signature .= " * Returns metadata about " . $column . "\n";
				$signature .= " * \n";
				$signature .= " * @param  string \$element  The element to return. Must be one of: 'type', 'not_null', 'default', 'valid_values', 'max_length', 'feature', 'directory'.\n";
				$signature .= " * @return mixed  The metadata array or a single element\n";
				$signature .= " */\n";
			}
			$inspect_method = 'inspect' . $camelized_column;
			$signature .= 'public function ' . $inspect_method . '($element=NULL)';
			
			$signatures[$inspect_method] = $signature;
		}
	}
	
	
	/**
	 * Rolls back a transaction, or decreases the level
	 * 
	 * @internal
	 * 
	 * @return void
	 */
	static public function rollback()
	{
		// If the transaction was started by something else, don't even track it
		if (self::$transaction_level == 0) {
			return;
		}
		
		self::$transaction_level--;
		
		if (!self::$transaction_level) {
			fFilesystem::rollback();
		}
	}
	
	
	/**
	 * Copies a file from the filesystem to the file upload directory and sets it as the file for the specified column
	 * 
	 * This method will perform the fImage calls defined for the column.
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object            The fActiveRecord instance
	 * @param  array         &$values           The current values
	 * @param  array         &$old_values       The old values
	 * @param  array         &$related_records  Any records related to this record
	 * @param  string        &$method_name      The method that was called
	 * @param  array         &$parameters       The parameters passed to the method
	 * @return void
	 */
	static public function set($object, &$values, &$old_values, &$related_records, &$method_name, &$parameters)
	{
		$class = get_class($object);
		
		list ($action, $column) = fORM::parseMethod($method_name);
		
		$doc_root = realpath($_SERVER['DOCUMENT_ROOT']);
		
		if (!array_key_exists(0, $parameters)) {
			fCore::toss(
				'fProgrammerException',
				fGrammar::compose(
					'The method %s requires exactly one parameter',
					fCore::dump($method_name) . '()'
				)
			);
		}
		
		$file_path    = $parameters[0];
		$invalid_file = !fCore::stringlike($file_path);
		
		if (!$file_path || (!file_exists($file_path) && !file_exists($doc_root . $file_path))) {
			fCore::toss(
				'fEnvironmentException',
				fGrammar::compose(
					'The file specified, %s, does not exist. This may indicate a missing enctype="multipart/form-data" attribute in form tag.',
					fCore::dump($file_path)
				)
			);
		}
		
		if (!file_exists($file_path) && file_exists($doc_root . $file_path)) {
			$file_path = $doc_root . $file_path;
		}
		
		$file     = new fFile($file_path);
		$new_file = $file->duplicate(self::$file_upload_columns[$class][$column]);
		
		fActiveRecord::assign($values, $old_values, $column, $new_file);
		
		self::processImage($class, $column, $new_file);
	}
	
	
	/**
	 * Sets up the {@link fUpload} class for a specific column
	 * 
	 * @param  string $class   The class to set up for
	 * @param  string $column  The column to set up for
	 * @return void
	 */
	static private function setUpFUpload($class, $column)
	{
		fUpload::reset();
		
		// Set up the fUpload class
		if (!empty(self::$fupload_method_calls[$class][$column])) {
			foreach (self::$fupload_method_calls[$class][$column] as $method_call) {
				if (!is_callable($method_call['callback'])) {
					fCore::toss(
						'fProgrammerException',
						fGrammar::compose(
							'The fUpload method specified, %s, is not a valid method.',
							fCore::dump($method_call['method']) . '()'
						)
					);
				}
				call_user_func_array($method_call['callback'], $method_call['parameters']);
			}
		}
	}
	
	
	/**
	 * Uploads a file
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object            The fActiveRecord instance
	 * @param  array         &$values           The current values
	 * @param  array         &$old_values       The old values
	 * @param  array         &$related_records  Any records related to this record
	 * @param  string        &$method_name      The method that was called
	 * @param  array         &$parameters       The parameters passed to the method
	 * @return void
	 */
	static public function upload($object, &$values, &$old_values, &$related_records, &$method_name, &$parameters)
	{
		$class = get_class($object);
		
		list ($action, $column) = fORM::parseMethod($method_name);
		
		self::setUpFUpload($class, $column);
		
		$upload_dir = self::$file_upload_columns[$class][$column];
		
		// Let's clean out the upload temp dir
		try {
			$temp_dir = new fDirectory($upload_dir->getPath() . self::TEMP_DIRECTORY);
		} catch (fValidationException $e) {
			$temp_dir = fDirectory::create($upload_dir->getPath() . self::TEMP_DIRECTORY);
		}
		
		$temp_files = $temp_dir->scan();
		foreach ($temp_files as $temp_file) {
			if (filemtime($temp_file->getPath()) < strtotime('-6 hours')) {
				unlink($temp_file->getPath());
			}
		}
		
		// Try to upload the file putting it in the temp dir incase there is a validation problem with the record
		try {
			$file = fUpload::upload($temp_dir, $column);
			fUpload::reset();
		
		// If there was an eror, check to see if we have an existing file
		} catch (fExpectedException $e) {
			fUpload::reset();
			
			// If there is an existing file and none was uploaded, substitute the existing file
			$existing_file = fRequest::get('__flourish_existing_' . $column);
			$delete_file   = fRequest::get('__flourish_delete_' . $column, 'boolean');
			$no_upload     = $e->getMessage() == fGrammar::compose('Please upload a file');
			
			if ($existing_file && $delete_file && $no_upload) {
				$file = NULL;
				
			} elseif ($existing_file) {
				
				$file = new fFile($upload_dir->getPath() . $existing_file);
				
				$current_file = $values[$column];
				if (!$current_file || ($current_file && $file->getPath() != $current_file->getPath())) {
					fActiveRecord::assign($values, $old_values, $column, $file);
				}
				return;
				
			} else {
				return;
			}
		}
		
		// Assign the file
		fActiveRecord::assign($values, $old_values, $column, $file);
		
		// Perform the file upload inheritance
		if (!empty(self::$column_inheritence[$class][$column])) {
			foreach (self::$column_inheritence[$class][$column] as $other_column) {
				
				if ($file) {
					// Let's clean out the upload temp dir
					try {
						$other_upload_dir = self::$file_upload_columns[$class][$other_column];
						$other_temp_dir   = new fDirectory($other_upload_dir->getPath() . self::TEMP_DIRECTORY);
					} catch (fValidationException $e) {
						$other_temp_dir   = fDirectory::create($other_upload_dir->getPath() . self::TEMP_DIRECTORY);
					}
					
					$temp_files = $other_temp_dir->scan();
					foreach ($temp_files as $temp_file) {
						if (filemtime($temp_file->getPath()) < strtotime('-6 hours')) {
							unlink($temp_file->getPath());
						}
					}
					
					$other_file = $file->duplicate($other_temp_dir, FALSE);
				} else {
					$other_file = $file;
				}
				
				fActiveRecord::assign($values, $old_values, $other_column, $other_file);
				
				if ($other_file) {
					self::processImage($class, $other_column, $other_file);
				}
			}
		}
		
		// Process the file
		if ($file) {
			self::processImage($class, $column, $file);
		}
	}
	
	
	/**
	 * Moves uploaded file from the temporary directory to the permanent directory
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object                The fActiveRecord instance
	 * @param  array         &$values               The current values
	 * @param  array         &$old_values           The old values
	 * @param  array         &$related_records      Any records related to this record
	 * @param  array         &$validation_messages  The existing validation messages
	 * @return void
	 */
	static public function validate($object, &$values, &$old_values, &$related_records, &$validation_messages)
	{
		$class = get_class($object);
		
		foreach (self::$file_upload_columns[$class] as $column => $directory) {
			$column_name = fORM::getColumnName($class, $column);
			
			$search_message  = fGrammar::compose('%s: Please enter a value', $column_name);
			$replace_message = fGrammar::compose('%s: Please upload a file', $column_name);;
			$validation_messages = str_replace($search_message, $replace_message, $validation_messages);
			
			self::setUpFUpload($class, $column);
			
			// Grab the error that occured
			try {
				if (fUpload::check($column)) {
					fUpload::validate($column);
				}
			} catch (fValidationException $e) {
				if ($e->getMessage() != fGrammar::compose('Please upload a file')) {
					$validation_messages[] = $column_name . ': ' . $e->getMessage();
				}
			}
		}
	}
	
	
	/**
	 * Forces use as a static class
	 * 
	 * @return fORMFile
	 */
	private function __construct() { }
}



/**
 * Copyright (c) 2008 William Bond <will@flourishlib.com>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */