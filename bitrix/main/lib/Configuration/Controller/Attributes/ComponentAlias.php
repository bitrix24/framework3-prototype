<?php

namespace Bitrix\Main\Lib\Configuration\Controller\Attributes;

#[\Attribute]
class ComponentAlias
{
	public function __construct(string ...$names)
	{
	}
}