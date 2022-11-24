<?php

use Bitrix\Main\Lib\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

	$routes
		->prefix('shopforum')
		->solution(\Bitrix\Main\Solutions\Forum\ForumSolution::class);

	$routes
		->prefix('users')
		->solution(\Bitrix\Main\Solutions\Users\UsersSolution::class);

	$routes
		->get('/about', 'about.twig');

};