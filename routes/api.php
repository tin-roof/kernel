<?php
use Packages\Kernel\Route as Route;

Route::get('/:link', function ($link) {
	echo 'link id - '. $link;
	exit;
});
