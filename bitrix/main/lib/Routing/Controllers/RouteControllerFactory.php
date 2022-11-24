<?php

namespace Bitrix\Main\Lib\Routing\Controllers;

use Bitrix\Main\Lib\Exceptions\ArgumentException;
use DI\Container;

class RouteControllerFactory
{
	protected Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function create(mixed $controllerDefinition): Controller
	{
		if ($controllerDefinition instanceof Controller)
		{
			$controller = $controllerDefinition;
		}
		elseif ($controllerDefinition instanceof \Closure)
		{
			$controller = $this->container->make(ClosureController::class, ['closure' => $controllerDefinition]);
		}
		elseif (is_array($controllerDefinition))
		{
			$controller = $this->container->make(EngineController::class, ['callback' => $controllerDefinition]);
		}
		elseif (is_string($controllerDefinition))
		{
			if (str_ends_with($controllerDefinition, '.twig'))
			{
				$controller = $this->container->make(TwigPageController::class, ['path' => $controllerDefinition]);
			}
			else
			{
				$controller = $this->container->make(EngineActionController::class, ['actionClass' => $controllerDefinition]);
			}
		}
		else
		{
			throw new ArgumentException(sprintf(
				'Unknown route controller `%s`', gettype($controllerDefinition)
			));
		}

		return $controller;
	}
}