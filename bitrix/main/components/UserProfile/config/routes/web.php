<?php

use Bitrix\Main\Lib\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

//    $routes->get('', function () {
//        echo 'nothing ';
//    })->name('nothing');

    $routes->get('{id}', function () {
        echo 'profile ' . \Bitrix\Main\Lib\Context::getRoute()->getParameterValue('id');
        var_dump(\Bitrix\Main\Lib\Context::getRoute()->getParametersValues());
    })->name('profile');

    $routes->get('{id}/calendar', function () {
        echo 'calendar ' . \Bitrix\Main\Lib\Context::getRoute()->getParameterValue('id');
        var_dump(\Bitrix\Main\Lib\Context::getRoute()->getParametersValues());
    })->name('calendar');

	$routes->get('{id}/news', function () {
        echo 'news ' . \Bitrix\Main\Lib\Context::getRoute()->getParameterValue('id');
        var_dump(\Bitrix\Main\Lib\Context::getRoute()->getParametersValues());
    })->name('news');
};