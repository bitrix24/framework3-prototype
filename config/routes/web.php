<?php

use Bitrix\Main\Lib\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

	$routes->fallbackNotFound(function () {
		echo 'not found :(';
	}); // for a specific site?

	// for dev environment
	$routes->get(
		'/resources/{path}',
		[\Bitrix\Main\Lib\StaticController::class, 'resolveAction']
	)->where('path', '.*');

	$routes
		->prefix('myshop')
		->site(\Sites\s3\Site::class);
};