<?php

error_reporting(E_ALL);
require __DIR__ . '/bootstrap.php';

$view = new \IOguns\SimpleView\View();
$view->setDirectory(__DIR__ . '/layouts/');

echo 'starting to render child_0' . PHP_EOL;
$view->setView('child_0');
echo $view->render();
