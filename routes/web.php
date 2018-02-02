<?php
use Packages\Kernel\Route as Route;

Route::get('/', function () {
	return Packages\Kernel\View::make('welcome');
});

Route::get('/joe/:link', function ($link) {
	echo 'link id - '. $link;
	exit;
});

Route::get('/:link', function ($link) {
	echo 'link id - '. $link;
	exit;
});
