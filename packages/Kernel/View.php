<?php
namespace Packages\Kernel;

/**
 * Views 
 *    Make the views accesible to the controllers
 *    Esentially build the templating engine.
 */
class View
{
	/**
	 * Make the view
	 *    Find the view file and read it into a string
	 *    Replace the variables with their values
	 *    @TODO: Add on the rest of the templates
	 *
	 * @param string $view
	 * @param array $variables
	 */
	public function make($view = '', $variables = []) {
		if (file_exists(VIEWS . $view . '.php')) {
			ob_start();
			require(VIEWS . $view . '.php');
			$file = ob_get_clean();
			self::parseVariables($file, $variables);
			echo $file;
		} else {
			echo 'failure';
		}
	}

	/**
	 * Check the file string for any variables that exist in the class and replace them with their value
	 * 
	 * @param string $file
	 * @param array $variables
	 */
	private static function parseVariables(&$file, $variables) {
		$find = [];
		$replace = [];
		foreach ($variables as $variable => $value) {
			$find[] = '{{ $' . $variable . ' }}';
			$replace[] = $value;
		}
		$file = str_replace($find, $replace, $file);
	}
}