<?php 
// config files
require __DIR__ . '/../config/app.conf.php';
require __DIR__ . '/../config/database.conf.php';

// auto loader
require __DIR__ . '/../packages/Kernel/Autoloader.php';

// create the app
$app = new \Packages\Kernel\Kernel;

// start the app
$app->init();