<?php

use Bitrix\Main\Lib\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

	$routes
		->get('/about', function () { return 'custom about';});

};