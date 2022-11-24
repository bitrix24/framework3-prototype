<?php

use Bitrix\Main\Lib\Configuration\Controller\Attributes\ComponentAlias;
use Bitrix\Main\Lib\Configuration\Controller\Attributes\RouteName;

return [

	#[RouteName('forum_main')]
	#[ComponentAlias('themain')]
	function (Bitrix\Main\Components\DemoForum\Controller $controller)
	{
		$controller->perPage = 120;
	},

	function (Bitrix\Main\Components\DemoForumThread\Controller $controller)
	{
		$controller->perPage = 120;
	},

	function (\Bitrix\Main\Components\Feedback\Controller $controller)
	{
		$controller->showExpanded = true;
	}

//	#[RouteName('forum_thread', 'forum_section')]
//	#[ComponentAlias('clientForumComponent1', 'clientForumComponent2')]   // AND or OR
//	function (Bitrix\Main\Components\DemoForumThread\Controller $controller)
//	{
//		$controller->perPage = 20;
//	},

];



