<?php

use Bitrix\Main\Lib\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

    $routes->get('/', [
        \Bitrix\Main\Components\DemoUsers\Controller::class,
        'listAction',
    ])->name('list');

    $routes
        ->name('user')
        ->component(\Bitrix\Main\Components\DemoUser\Controller::class);
};