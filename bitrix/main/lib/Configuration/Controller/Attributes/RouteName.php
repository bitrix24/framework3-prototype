<?php

namespace Bitrix\Main\Lib\Configuration\Controller\Attributes;

#[\Attribute]
class RouteName
{
	public function __construct(string ...$names)
	{
	}
}