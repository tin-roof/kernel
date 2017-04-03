<?php
/**
 * Autoload Classes when they are needed for the app
 * 
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {
	// Replace the \ with / in the class name and add the file extension to generate full file path

	$file = ROOT_DIR . str_replace('\\', '/', $class) . '.php';

	// if the file exists, require it
	if (file_exists($file)) {
		require $file;
	}
});