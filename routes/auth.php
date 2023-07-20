<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
|
| Here is registered all of the auth routes for an application.
|
*/

$router->post("/login", "AuthController@login");
$router->post("/register", "AuthController@register");

$router->group(["middleware" => "auth"], function($router) {
    $router->post("/logout", "AuthController@logout");
    $router->get("/refresh", "AuthController@refresh"); 
    $router->post("/refresh", "AuthController@refresh");
});