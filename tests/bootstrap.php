<?php

\set_time_limit(0);
\error_reporting(E_ALL);


if (!\is_dir(__DIR__ . '/../vendor/')) {
    \exec('cd ../ && composer update');
}

require __DIR__ . '/../vendor/autoload.php';
