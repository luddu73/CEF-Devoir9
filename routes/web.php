<?php

function callController(string $controller, string $method): callable
{
    $fqcn = "Touchepasauklaxon\\Controllers\\$controller";
    return function (...$params) use ($fqcn, $method) {
        (new $fqcn())->$method(...$params);
    };
}

$router->get('/', callController('HomeController', 'index'));
$router->get('/login', callController('HomeController', 'login'));
$router->post('/login', callController('AuthController', 'login'));
$router->get('/logout', callController('AuthController', 'logout'));
$router->get('/creer', callController('HomeController', 'createTrajetForm'));
$router->get('/modifier/:id', callController('HomeController', 'editTrajetForm'));
$router->post('/add', callController('HomeController', 'createTrajet'));
$router->patch('/update/:id', callController('HomeController', 'updateTrajet'));
$router->get('/detail/:id', callController('HomeController', 'viewTrajet'));
$router->post('/delete', callController('HomeController', 'deleteTrajet'));
$router->get('/admin', callController('AdminController', 'index'));
$router->get('/admin/agences', callController('AdminController', 'agences'));
$router->post('/admin/agences/add', callController('AdminController', 'addAgence'));
$router->post('/admin/agences/edit', callController('AdminController', 'editAgence'));
$router->post('/admin/agences/delete/', callController('AdminController', 'deleteAgence'));
$router->get('/admin/trajets', callController('AdminController', 'trajets'));
$router->post('/admin/trajets/delete/', callController('AdminController', 'deleteTrajet'));