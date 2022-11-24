<?php

use Bitrix\Main\Lib\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

    $routes->get('/', [
        \Bitrix\Main\Components\DemoNewsList\Controller::class,
        'listAction',
    ])->name('list');

    $routes
        ->name('article')
        ->component(\Bitrix\Main\Components\DemoNewsArticle\Controller::class);

};