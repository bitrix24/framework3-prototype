<?php

use Bitrix\Main\Lib\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

    $routes->get('/', [
        \Bitrix\Main\Components\DemoUsers\Controller::class,
        'listAction',
    ])->name('users_list');

    $routes->get('/{id}', [
        \Bitrix\Main\Components\DemoUser\Controller::class,
        'mainAction',
    ])->name('users_profile');
};