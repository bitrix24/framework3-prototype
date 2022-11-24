<?php

use Bitrix\Main\Lib\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

    $routes->get('{id}', [
        \Bitrix\Main\Components\DemoUser\Controller::class,
        'mainAction',
    ])->name('profile');
};