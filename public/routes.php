<?php

// define routes

$routes = array(
    array(
        "pattern" => "logout",
        "controller" => "auth",
        "action" => "logout"
    ),
    array(
        "pattern" => "privacy",
        "controller" => "home",
        "action" => "privacy"
    ),
    array(
        "pattern" => "termsofuse",
        "controller" => "home",
        "action" => "termsofuse"
    ),
    array(
        "pattern" => "home",
        "controller" => "home",
        "action" => "index"
    )
);

// add defined routes
foreach ($routes as $route) {
    $router->addRoute(new Framework\Router\Route\Simple($route));
}

// unset globals
unset($routes);
