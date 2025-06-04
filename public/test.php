<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Touchepasauklaxon\Controllers\HomeController;

$ctrl = new HomeController();
$ctrl->index();
