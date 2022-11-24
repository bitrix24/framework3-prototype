<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing;

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Exceptions\SystemException;
use Bitrix\Main\Lib\Routing\Controllers\RouteControllerFactory;

/**
 * @package    bitrix
 * @subpackage main
 *
 * @method RoutingConfiguration middleware($middleware)
 * @method RoutingConfiguration prefix($prefix)
 * @method RoutingConfiguration name($name)
 * @method RoutingConfiguration domain($domain)
 * @method RoutingConfiguration where($parameter, $pattern)
 * @method RoutingConfiguration default($parameter, $value)
 * @method RoutingConfiguration layout($name)
 * @method RoutingConfiguration bindSolution($solutionName, $currentSolutionName)
 * @method RoutingConfiguration bindRoute($routeName, $routeAlias)
 * @method RoutingConfiguration bindParameter($parameterName, $valueCallback)
 *
 * @method RoutingConfiguration get($uri, $controller)
 * @method RoutingConfiguration post($uri, $controller)
 * @method RoutingConfiguration any($uri, $controller)
 *
 * @method void group($callback)
 * @method void solution($solutionClass, $parameters = [])
 * @method void site($siteClass)
 */
class RoutingConfigurator
{
	/** @var Router */
	protected $router;

	/** @var Options Acts inside groups as a stack */
	protected $scopeOptions;

	/**
	 * RoutingConfigurator constructor.
	 */
	public function __construct()
	{
		$this->scopeOptions = new Options;
	}

	public function __call($method, $arguments)
	{
		// setting option
		if (in_array($method, Options::$optionList, true))
		{
			$configuration = $this->createConfiguration();
			return $configuration->$method(...$arguments);
		}

		// setting route
		if (in_array($method, RoutingConfiguration::$configurationList, true))
		{
			$configuration = $this->createConfiguration();
			return $configuration->$method(...$arguments);
		}

		throw new SystemException(sprintf(
			'Unknown method `%s` for object `%s`', $method, get_called_class()
		));
	}

	public function createConfiguration()
	{
		$configuration = Context::getContainer()->make(RoutingConfiguration::class);

		$configuration->setConfigurator($this);
		$this->router->registerConfiguration($configuration);

		$configuration->setOptions(clone $this->scopeOptions);

		return $configuration;
	}

	public function mergeOptionsWith($anotherOptions)
	{
		$this->scopeOptions->mergeWith($anotherOptions);
	}

	public function fallbackNotFound($controller)
	{
		$controller = Context::getContainer()->get(RouteControllerFactory::class)->create($controller);
		$this->router->setFallbackNotFound($controller);
	}

	/**
	 * @return Router
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * @param Router $router
	 */
	public function setRouter($router)
	{
		$this->router = $router;
	}

	public function __clone()
	{
		$this->scopeOptions = clone $this->scopeOptions;
		$this->scopeOptions->clearCurrent();
	}
}