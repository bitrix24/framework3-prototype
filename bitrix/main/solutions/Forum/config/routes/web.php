<?php

use Bitrix\Main\Lib\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

    $routes->get('/', [
        \Bitrix\Main\Components\DemoForum\Controller::class,
        'mainAction',
    ])->name('forum_main');

    $routes->get('/{sectionId}', [
        \Bitrix\Main\Components\DemoForumSection\Controller::class,
        'mainAction',
    ])->name('forum_section');

    $routes->get('/{sectionId}/thread/{threadId}', [
        \Bitrix\Main\Components\DemoForumThread\Controller::class,
        'mainAction',
        ])
            ->name('forum_thread');
           // ->bindParameter('threadId', function (ForumThread $thread) {return $thread->getId();});

    $routes->get('/{sectionId}/thread/{threadId}/message/{messageId}', [
        \Bitrix\Main\Components\DemoForumThread\Controller::class,
        'messageAction',
    ])->name('forum_message');
};