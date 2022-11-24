<?php

namespace Bitrix\Main\Layouts\LightV3;

use Bitrix\Main\Lib\Layout;

class LightV3Layout extends Layout
{
	public function getData()
	{
		return [
			'layout' => [
				'theme' => 'red',
			]
		];
	}
}