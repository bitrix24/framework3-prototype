<?php

use Bitrix\Main\Lib\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

    $routes->get('{id}', [
        \Bitrix\Main\Components\DemoNewsArticle\Controller::class,
        'detailAction',
    ])->name('detail');

    $routes->get('{id}/comments', [
        \Bitrix\Main\Components\DemoNewsArticle\Controller::class,
        'commentsAction',
    ])->name('comments');
};