<?php

error_reporting(E_ALL);
require __DIR__ . '/bootstrap.php';

$view = new \IOguns\SimpleView\View();
$view->setDirectory(__DIR__ . '/layouts/');

//display only the parent, child_2, child_1, child_0 views
$view = new \IOguns\SimpleView\View();
$view->setDirectory(__DIR__ . '/layouts/');
$view->setView('child_2');
echo $view->render();
