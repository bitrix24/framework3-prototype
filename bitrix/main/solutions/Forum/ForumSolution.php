<?php

namespace Bitrix\Main\Solutions\Forum;

class ForumSolution extends \Bitrix\Main\Lib\Solution
{
	public array $avatarSize = [200,200];

	public int $threadsPerPage = 20;

	public int $messagesPerPage = 20;

	public $allowSmiles;

	public $showSectionDescriptionOnMainPage;

	public function __construct()
	{
		$this->allowSmiles = function () {

		};
	}


	public function getParametersMap(): array
	{
		return [
			\Bitrix\Main\Components\DemoForumSection\Controller::class => [

			],
			\Bitrix\Main\Components\DemoForumThread\Controller::class => [
				'avatarSize' => $this->avatarSize,
				'perPage' => $this->messagesPerPage,
				'allowSmiles' => function () {
					// get option from db
					$this;
				}
			],
			\Bitrix\Main\Components\DemoForum\Controller::class => [
				'showSectionDescription' => $this->showSectionDescriptionOnMainPage
			]
		];
	}
}






