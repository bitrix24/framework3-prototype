<?php

namespace Bitrix\Main\Lib;

use Bitrix\Main\Lib\Controllers\Controller;
use Bitrix\Main\Lib\Routing\RoutablePackageTrait;

class Solution
{
	use RoutablePackageTrait;

	public function setParametersToController(Controller $controller): void
	{
		$controllerClass = get_class($controller);
		$parametersMap = $this->getParametersMap();

		if (isset($parametersMap[$controllerClass]))
		{
			$parameters = $parametersMap[$controllerClass];

			foreach ($parameters as $parameterName => $parameterValue)
			{
				if ($parameterValue instanceof \Closure)
				{
					$parameterValue = $parameterValue();
				}

				if ($parameterValue !== null)
				{
					$controller->{$parameterName} = $parameterValue;
				}
			}
		}
	}

	public function getParametersMap(): array
	{
		return [];
	}
}