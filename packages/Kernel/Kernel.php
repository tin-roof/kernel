<?php
namespace Packages\Kernel;

/**
 * Main App Class
 *   Start the main processes for the app to run (include required files and build the app object)
 */
class Kernel
{
    private $_router;

    public function __construct() {
        $this->_router = new \Packages\Kernel\Router;
    }

    /**
     * Anything that needs to happen in the app can start here
     */
    public function init() {
        $this->_router->route();
    }
}
