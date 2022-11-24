<?php

namespace Bitrix\Main\Lib\Controllers;

use Bitrix\Main\Lib\Configuration\Controller\ControllerConfiguration;
use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Routing\Route;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

class ControllerFactory
{
	protected Container $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @template T
	 * @param string|class-string<T> $controllerClass
	 * @param ?string $configurationAlias
	 * @return mixed|T
	 *
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function create(string $controllerClass, string $configurationAlias = null, Route $route = null): Controller
	{
		/** @var Controller $controller */
		$controller = $this->container->make($controllerClass);

		// 1. configuration alias
		if (!empty($configurationAlias))
		{
			$controller->setTemplateRouterContext(['configuration' => $configurationAlias]);
		}

		// 2. get solution from current route, get mapped parameters for this controller
		$route = $route ?: Context::getRoute();

		if ($route?->getOptions()->hasSolution())
		{
			$route->getOptions()->getSolution()->setParametersToController($controller);
		}

		// 3. custom configuration for controller
		$controllerConfiguration = $this->container->get(ControllerConfiguration::class);
		$controllerConfiguration->setConfiguration($controller, $configurationAlias);

		return $controller;
	}
}