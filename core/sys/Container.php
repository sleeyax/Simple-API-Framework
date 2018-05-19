<?php
// IOC/dependency container for controllers
class Container
{
    /**
     * @var array registered containers
     */
    private $containers = [];

    /**
     * Add controller object to container list
     * @param string $controller
     * @param array  $dependencies (optional)
     */
    public function load(string $controller, array $dependencies = null)
    {
        require_once("core/controllers/$controller.php");
        $this->containers[$controller] = $dependencies != null ? new $controller(extract($dependencies)) : new $controller();
    }

    /**
     * Call controller method
     * @param string $controller
     * @param string $method
     * @param array  $params
     */
    public function call(string $controller, string $method, array $params)
    {
        if (!in_array($controller, $this->containers)) {
            $this->load($controller);
        }

        call_user_func_array([$this->containers[$controller], $method], $params);
    }

    /**
     * Container instance singleton
     * @return Container
     */
    public static function getInstance()
    {
        $instance = null;
        if ($instance == null) {
            $instance = new self();
        }
        return $instance;
    }
}