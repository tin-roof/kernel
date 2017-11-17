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
	public static function make($view = '', $variables = []) {
		if (file_exists(VIEWS_DIR . $view . '.php')) {
			ob_start();
			require(VIEWS_DIR . $view . '.php');
			$file = ob_get_clean();

			// put together parts of the template
			self::buildTempalte($file);

			// replace the variables
			self::parseVariables($file, $variables);

			echo $file;
		} else {
			echo 'failure';
		}
	}

	/**
	 * Parse throught he file and build the whole page. Puts all the pieces together
	 *
	 * @param string $file template file that needs to be compiled
	 */
	private static function buildTempalte(&$file = '') {
		if (empty($file)) {
			return;
		}

		$page = $file;

		// get the extended part of the view
		$extendsRegex = '/@extends\(\'(.*?)\'\)/';
		preg_match_all($extendsRegex, $file, $extends);

		if (!empty($extends[1])) {
		    ob_start();
	 	    require(VIEWS_DIR . $extends[1][0] . '.php');
		    $page = ob_get_clean();
		}

		// get all the sections
		$sectionsRegex = '/@section\(\'(.*?)\'\)/';
		preg_match_all($sectionsRegex, $file, $sections);
		foreach($sections[1] as $section) {
			$sectionParts = explode('\',\'', str_replace('\', \'', '\',\'', $section));

			if (count($sectionParts) > 1) {
				$page = str_replace('@yield(\'' . $sectionParts[0] . '\')', $sectionParts[1], $page);
				continue;
			}

			$contentRegex = '/(?<=@section\(\'' . $sectionParts[0] . '\'\)).*?(?=@endsection)/s';
			preg_match_all($contentRegex, $file, $content);
			$page = str_replace('@yield(\'' . $sectionParts[0] . '\')', $content[0][0], $page);

		}

		// get all the includes
		$includesRegex = '/@include\(\'(.*?)\'\)/';
		preg_match_all($includesRegex, $page, $includes);
		foreach($includes[1] as $include) {
			ob_start();
			require(VIEWS_DIR . $include . '.php');
			$includeFile = ob_get_clean();

			$page = str_replace('@include(\'' . $include . '\')', $includeFile, $page);
		}

		$file = $page;
		return;
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
