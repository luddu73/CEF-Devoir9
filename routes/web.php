<?php

function callController(string $controller, string $method)
{
    $fqcn = "Touchepasauklaxon\\Controllers\\$controller";
    return function () use ($fqcn, $method) {
        (new $fqcn())->$method();
    };
}

$router->get('/', function(){
    echo "Bienvenue sur la page d'accueil !";
});

$router->get('/home', callController('HomeController', 'index'));
$router->get('/test-bdd', callController('HomeController', 'testBdd'));
$router->post('/login', callController('AuthController', 'login'));
$router->get('/logout', callController('AuthController', 'logout'));
$router->get('/dashboard', callController('DashboardController', 'index'));