<?php 
use Packages\Kernel\Route as Route;

Route::get('/', function () {
	return Packages\Kernel\View::make('welcome');
});