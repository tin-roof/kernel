<?php
/**
 * Autoload Classes when they are needed for the app
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {
	// replace \ with / and add the extenstion
	$file = ROOT_DIR . str_replace('\\', '/', $class) . '.php';

	// replace core file system folders with lower case names
	$file = str_replace_first('/Packages/', '/packages/', $file);
	$file = str_replace_first('/App/', '/app/', $file);


	// if the file exists, require it
	if (file_exists($file)) {
		require $file;
	}
});

/**
 * Replace the first occurance of a substring with a new string
 *
 *    @TODO: Possibly move this to a different location
 *
 * @param string $find
 * @param string $replace
 * @param string $string
 */
function str_replace_first($find, $replace, $string) {
	$pos = strpos($string, $find);
	if ($pos !== false) {
		return substr_replace($string, $replace, $pos, strlen($find));
	}
	return $string;
}
