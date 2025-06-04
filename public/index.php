<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/database.php';

$router = new \Buki\Router\Router([]);

require_once dirname(__DIR__) . '/routes/web.php';

$router->run();