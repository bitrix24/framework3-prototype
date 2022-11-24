<?php

namespace Bitrix\Main\Lib\Routing;

use Bitrix\Main\Lib\PackageLeadClassTrait;

trait RoutablePackageTrait
{
	use PackageLeadClassTrait;

	public function getRoutes()
	{
		$routesDir = $this->getPath() . '/config/routes';
		$dirFiles = scandir($routesDir);
		$routeCallbacks = [];

		foreach ($dirFiles as $dirFile)
		{
			if (str_ends_with($dirFile, '.php'))
			{
				$routeCallbacks[] = include $routesDir . '/' . $dirFile;
			}
		}

		return $routeCallbacks;
	}
}