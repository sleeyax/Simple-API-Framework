<?php
require_once 'Container.php';
class Route {
    /**
     * @var string current URL (in browser)
     */
    private $request;

    /**
     * @var array all registered routes
     */
    private $routes = [];

    /**
     * @var string part of the route which should be the API key
     */
    private $apiKeyRoutePart;

    /**
     * @var array available dynamic route patterns
     */
    private $urlPatterns = [
        ':any' => '.+',
        ':num' => '\d+',
        ':str' => '[a-zA-Z_ ]+',
        ':strnum' => '[a-zA-Z0-9]+',
        ':apikey' => 'yourapikeyhere'
    ];

    /**
     * @var object IOC container instance
     */
    private $container;

    /**
     * Route constructor.
     * @param null $container IOC container to use
     */
    public function __construct($container = null)
    {
        $this->container = $container != null ? $container : Container::getInstance();
    }

    /**
     * Set API key route part to append to routes
     * @param $keyroute
     * @example user/name/{:str} -> user/name/{:str}/key/{:strnum}
     */
    public function setApiKeyRoutePart($keyroute) {
        $this->apiKeyRoutePart = $this->formatSlashes($keyroute);
    }

    /**
     * Register route
     * @param string $route
     * @param        $callback
     */
    public function register(string $route, $callback)
    {
        // Add slash to back of url
        $this->request =  $_GET['request'] ?? '/';
        $this->request = $this->formatSlashes($this->request);
        $route = $this->formatSlashes($route);
        $route .= $this->apiKeyRoutePart ?? ''; // When using API keys, append API key route part to every route
        $callbackParams = [];

        // -- Check for dynamic routes --
        // 1) convert $url to regex matchable
        // controllers/user/{name} => controllers\/user\/(.+)
        // 2) match regex with $request (convert "/" to "\/"!)
        // preg_match('/controllers\/user\/(.+)/', 'controllers/user/sleeyax', $matches)
        // 3) set current $route to regex's full capture match & set other groups as callbackArgs
        // ...

        // -- Dynamic routes --
        // Convert route to regex matchable
        $regexMatchable = preg_replace_callback(
            '/{(.+?)}/',
            function($matches) {
                $matchType = $matches[1];
                if (!array_key_exists($matchType, $this->urlPatterns)) {exit("'$matchType' is not a valid url pattern!");}
                return '(' . $this->urlPatterns[$matchType] . '?)';
            },
            str_replace('/', '\/', $route)
        );

        // Match regex with request and set route to capt. grp 0 & callbackParams to other capt. grps
        if (preg_match("/$regexMatchable/", $this->request, $matches)) {
            $route = $matches[0];
            $callbackParams = array_slice($matches, 1);
        }

        // Execute callback & update valid routes
        if ($this->request == $route)
        {
            if (is_string($callback)) {
                // Split 'Controller@method'
                $split = explode('@', $callback);
                $this->container->call($split[0], $split[1], $callbackParams);
            }else{
                call_user_func_array($callback, $callbackParams);
            }

            $this->routes[] = $route;
        }
    }

    /**
     * Add ending slash (/) at ending of a URL
     * @param $url
     * @return string
     */
    private function formatSlashes($url)
    {
        return substr($url, -1) != '/' ? $url . '/' : $url;
    }

    /**
     * Throw 404 if route doesn't exist
     */
    public function validateRoutes()
    {
        if (!in_array($this->request, $this->routes)) {
            header("HTTP/1.0 404 Not Found");
            echo file_get_contents("core/views/404.html");
            exit;
        }
    }
}