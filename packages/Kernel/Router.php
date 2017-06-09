<?php
namespace Packages\Kernel;

/**
 * Kernel Router
 *    Find out where the user is trying to go and direct them to the right place
 */
class Router
{
	private $_routes = [];
	public function __construct() {
		$route = new Route;
		$this->_routes = $route->getRoutes();
	}

	/**
	 * Get the request uri and seperate it in to pieces
	 *    call the run function with the pieces
	 */
	public function route() {
		$method = $_SERVER['REQUEST_METHOD'];
		$request = explode('?', $_SERVER['REQUEST_URI'])[0];

		// set the request
		if (empty($request)) {
			$uri = '/';
		}
		$this->runRoute($method, $request);
	}

	/**
	 * Find the route in the routes list if it exits and run the controller and function listed in the route
	 *    also uses closure if it exists
	 * 
	 * @param string $method
	 * @param string $request
	 */
	private function runRoute($method, $request) {
		// return error if the route isnt found
		if (empty($this->_routes[$method][$request])) {
			echo "the route you are looking for doesnt exist";
			exit;
		}

		if (is_object($this->_routes[$method][$request])) {
			$route = $this->_routes[$method][$request];
			echo $route();
			exit;
		} else if (is_string($this->_routes[$method][$request])) {
			// break down the route to usable pieces
			$route = explode('@', $this->_routes[$method][$request]);
		} else {
			echo "you are pointing at nothing";
		}

		// instantiate the class
		$class = 'App\\Controllers\\' . $route[0];
		$class = new $class;

		// get the function name
		$function = $route[1];

		// echo the function call - should always be a string
		echo $class->$function();
		exit;
	}
}