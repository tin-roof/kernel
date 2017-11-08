<?php
namespace Packages\Kernel;

/**
 * Route Class
 *    Handle sorting the routes that are stored in the route files
 *    @TODO: add a way to dynamically ad prefix
 *    @TODO: add optional params in the url
 */
class Route
{
	private $_routes;
	private $_prefix = '';

	public function __construct() {
		$this->getWebRoutes();
		$this->getAPIRoutes();
	}

	/**
	 * Return the routes that match the type
	 *
	 * @param string $type what type of routes default all
	 *
	 * @return array the routes that match or all of them
	 */
	public function getRoutes($type = 'all') {
		if (!empty($this->_routes[$type])) {
			return $this->_routes[$type];
		} else {
			return $this->_routes;
		}
	}

	/**
	 * Build the routes that use the GET request type and add it to the routes array
	 *
	 * @param string $uri
	 * @param string $function
	 */
	public function get($uri, $function) {
		$uri = $this->checkUri($uri);
		$this->_routes['GET'][$this->_prefix . $uri['uri']] = [
			'uri' => $uri['uri'],
			'function' => $function,
			'variables' => $uri['variables']
		];
	}

	/**
	 * Build the routes that use the POST request type and add it to the routes array
	 *
	 * @param string $uri
	 * @param string $function
	 */
	public function post($uri, $function) {
		$uri = $this->checkUri($uri);
		$this->_routes['POST'][$this->_prefix . $uri['uri']] = [
			'uri' => $uri['uri'],
			'function' => $function,
			'variables' => $uri['variables']
		];
	}

	/**
	 * Build the routes that use the PUT request type and add it to the routes array
	 *
	 * @param string $uri
	 * @param string $function
	 */
	public function put($uri, $function) {
		$uri = $this->checkUri($uri);
		$this->_routes['PUT'][$this->_prefix . $uri['uri']] = [
			'uri' => $uri['uri'],
			'function' => $function,
			'variables' => $uri['variables']
		];
	}

	/**
	 * Build the routes that use the DELETE request type and add it to the routes array
	 *
	 * @param string $uri
	 * @param string $function
	 */
	public function delete($uri, $function) {
		$uri = $this->checkUri($uri);
		$this->_routes['DELETE'][$this->_prefix . $uri['uri']] = [
			'uri' => $uri['uri'],
			'function' => $function,
			'variables' => $uri['variables']
		];
	}

	/**
	 * Check the uri for any variables
	 *
	 * @param string $uri uri to Check
	 * @return array
	 */
	 private function checkUri($uri = '') {
		 if (empty($uri)) {
			 return ['uri' => ['/'], variables => []];
		 }

		 $variables = [];

		 $uriParts = explode('/', $uri);
		 foreach ($uriParts as $key => $piece) {
			 if (strpos($piece, ':') === 0) {
				 $variables[$key] = ltrim($piece, ':');
			 }
		 }

		 // remove the colon from the uri
		 $uri = implode('/', $uriParts);

		 return ['uri' => $uri, 'variables' => $variables];
	 }

	/**
	 * Read the web routes file
	 */
	private function getWebRoutes() {
		$this->_prefix = '';
		require ROUTES . '/web.php';
	}

	/**
	 * Read the api routes file
	 */
	private function getAPIRoutes() {
		$this->_prefix = '/api';
		require ROUTES . '/api.php';
	}
}
