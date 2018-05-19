<?php

require_once 'sys/Route.php';

$route = new Route();

// Make sure every route contains a key routepart
// $route->setApiKeyRoutePart('key/{:apikey}');

$route->register('user/name/{:str}', 'Controller@method');
$route->register('say/{:any}', function($msg) {
    echo $msg;
});

// throw error if route doesn't exist
$route->validateRoutes();