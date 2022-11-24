<?php

namespace Bitrix\Main\Lib\Configuration\Controller;

use Bitrix\Main\Lib\Configuration\Configuration;
use Bitrix\Main\Lib\Configuration\Controller\Attributes\ComponentAlias;
use Bitrix\Main\Lib\Configuration\Controller\Attributes\RouteName;
use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Controllers\Controller;
use ReflectionFunction;

class ControllerConfiguration
{
	protected array $globalScope = [];

	protected array $routeNameScope = [];

	protected array $componentAliasScope = [];

	protected bool $loaded = false;

	public function setConfiguration(Controller $controller, $componentAlias = null): void
	{
		$this->loadConfiguration();

		// search by index for current class with current route scope
		$controllerClass = get_class($controller);
		$routeName = Context::getRoute()?->getOptions()->getFullName();

		/** @var ConfigurationItem[] $configItems */
		$configItems = [];

		// 1st level filter
		if ($routeName > '')
		{
			if (!empty($this->routeNameScope[$controllerClass][$routeName]))
			{
				$configItems = array_merge($configItems, $this->routeNameScope[$controllerClass][$routeName]);
			}
		}

		if ($componentAlias  > '')
		{
			if (!empty($this->componentAliasScope[$controllerClass][$componentAlias]))
			{
				$configItems = array_merge($configItems, $this->componentAliasScope[$controllerClass][$componentAlias]);
			}
		}

		// 2nd level filter
		foreach ($configItems as $k => $configItem)
		{
			if ($routeName > '' || !empty($configItem->getRouteNames()))
			{
				if (!in_array($routeName, $configItem->getRouteNames()))
				{
					unset($configItems[$k]);
				}
			}

			if ($componentAlias > '' || !empty($configItem->getComponentAliases()))
			{
				if (!in_array($componentAlias, $configItem->getComponentAliases()))
				{
					unset($configItems[$k]);
				}
			}
		}

		// global
		if (!empty($this->globalScope[$controllerClass]))
		{
			$configItems = array_merge($this->globalScope[$controllerClass], $configItems);
		}

		// configure controller
		foreach ($configItems as $configItem)
		{
			$configItem->getCallback()($controller);
		}
	}

	public function loadConfiguration()
	{
		if ($this->loaded)
		{
			return;
		}

		// get callbacks from files
		$configuration = Context::getContainer()->get(Configuration::class);
		$configFiles = [PROJECT_ROOT.'/config/controllers/forum.php']; // TODO all files from dir

		foreach ($configFiles as $configFile)
		{
			$callbacks = include $configFile;

			foreach ($callbacks as $callback)
			{
				$reflection = new ReflectionFunction($callback);
				$attributes = $reflection->getAttributes();

				$configurationItem = new ConfigurationItem();
				$configurationItem->setCallback($callback);

				// controller class
				$parameter = current($reflection->getParameters());
				$controllerClass = $parameter->getType()->getName();

				// global scope
				if (empty($attributes))
				{
					$this->globalScope[$controllerClass][] = $configurationItem;
				}

				// attributes
				foreach ($attributes as $attribute)
				{
					switch ($attribute->getName())
					{
						case RouteName::class:
							$routeNames = $attribute->getArguments();
							$configurationItem->setRouteNames($routeNames);

							foreach ($routeNames as $routeName)
							{
								$this->routeNameScope[$controllerClass][$routeName][] = $configurationItem;
							}

							break;

						case ComponentAlias::class:
							$componentAliases = $attribute->getArguments();
							$configurationItem->setComponentAliases($componentAliases);

							foreach ($componentAliases as $componentAlias)
							{
								$this->componentAliasScope[$controllerClass][$componentAlias][] = $configurationItem;
							}

							break;
					}
				}
			}
		}

		$this->loaded = true;

		// TODO put in cache
	}
}