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

		try {
			$this->runRoute($method, $request);
		} catch (\Exception $e) {
			echo $e;
			exit;
		}
	}

	/**
	 * Find the route in the routes list if it exits and run the controller and function listed in the route
	 *    also uses closure if it exists
	 *
	 * @param string $method
	 * @param string $request
	 */
	private function runRoute($method, $request) {
		$variables = [];

		// return error if the route isnt found
		if (empty($this->_routes[$method][$request])) {
			$route = $this->validateRequest($method, $request);
			if (empty($route)) {
				throw new \Exception('The route you are looking for doesnt exist');
				exit;
			}
		} else {
			$route = [
				'uri' => $request,
				'function' => $this->_routes[$method][$request]
			];

		}

		$route['variables'] = $this->getVariables($request, $route['variables']);

		if (is_object($route['function'])) {
			echo call_user_func_array($route['function'], $route['variables']);
			exit;
		} else if (is_string($route['function'])) {
			// break down the route to usable pieces
			$method = explode('@', $route['function']);
		} else {
			echo "you are pointing at nothing";
			exit;
		}

		// instantiate the class
		$class = 'App\\Controllers\\' . $method[0];
		$class = new $class;

		// get the function name
		$function = $method[1];

		// echo the function call - should always be a string
		echo call_user_func_array([$class, $method], $variables);
		echo $class->$function();
		exit;
	}

	/**
	 * Find matching route
	 *
	 * @param string $method request method
	 * @param string $request uri request
	 * @return array
	 */
	private function validateRequest($method, $request) {
		$userRequest = explode('/', $request);
		$userRequestCount = count($userRequest);
		foreach ($this->_routes[$method] as $routeUri => $route) {
			$routeParts = explode('/', $routeUri);
			$routePartsCount = count($routeParts);
			$hasOptionalParams = (strpos($routeUri, '(') === false) ? false : true;

			// skip if the number of parts isnt the same
			if ($routePartsCount !== $userRequestCount) {
				continue;
			}

			// check to see if all the parts except the variables line up
			foreach ($routeParts as $routePartsKey => $part) {

				// skip it if its a variable
				if (strpos($part, ':') === 0) {
					if ($routePartsKey === ($routePartsCount - 1)) {
						return $route;
					}
					continue;
				}

				// break if they dont match
				if ($part !== $userRequest[$routePartsKey]) {
					break;
				}

				// return the route if its a match
				if ($routePartsKey === ($routePartsCount - 1)) {
					return $route;
				}
			}
		}

		return [];
	}

	/**
	 * Define the variables from the route
	 *
	 * @param string $request request uri
	 * @param array $routeVariables variables that are defined in the Route
	 * @return array
	 */
	private function getVariables($request, $routeVariables) {
		$variables = [];
		$request = explode('/', $request);

		foreach ($routeVariables as $key => $variable) {
			$variables[$variable] = $request[$key+1];
		}

		return $variables;
	}
}
