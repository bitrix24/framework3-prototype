<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing;

use Bitrix\Main\Lib\Exceptions\SystemException;
use Bitrix\Main\Lib\Routing\Controllers\RouteControllerFactory;
use Bitrix\Main\Lib\Site;
use Bitrix\Main\Lib\Solution;

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
 */
class RoutingConfiguration
{
	/** @var RoutingConfigurator */
	protected $configurator;

	/** @var Route|\Closure One route or group of routes */
	protected $routeContainer;

	/** @var Options */
	protected $options;

	/** @var callable[] */
	protected $customOptions = [];

	protected RouteControllerFactory $routeControllerFactory;

	public static $configurationList = [
		'get', 'post', 'any', 'group', 'solution', 'site',
	];

	public function __construct(RouteControllerFactory $routeControllerFactory)
	{
		$this->routeControllerFactory = $routeControllerFactory;
	}

	public function __call($method, $arguments)
	{
		// setting option
		if (in_array($method, Options::$optionList, true))
		{
			$this->options->$method(...$arguments);
			return $this;
		}

		throw new SystemException(sprintf(
			'Unknown method `%s` for object `%s`', $method, get_called_class()
		));
	}

	/**
	 * @param RoutingConfigurator $configurator
	 */
	public function setConfigurator($configurator)
	{
		$this->configurator = $configurator;
	}

	/**
	 * @param Options $options
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}

	public function get($uri, $controller)
	{
		$this->options->methods(['GET']);

		$route = new Route($uri, $this->routeControllerFactory->create($controller));
		$this->routeContainer = $route;

		return $this;
	}

	public function post($uri, $controller)
	{
		$this->options->methods(['POST']);

		$route = new Route($uri, $this->routeControllerFactory->create($controller));
		$this->routeContainer = $route;

		return $this;
	}

	public function any($uri, $controller)
	{
		$route = new Route($uri, $this->routeControllerFactory->create($controller));
		$this->routeContainer = $route;

		return $this;
	}

	public function group($callback)
	{
		$this->routeContainer = $callback;

		// add inner configuration to the router
		$subConfigurator = clone $this->configurator;
		$subConfigurator->mergeOptionsWith($this->options);

		// call
		$callback = $this->routeContainer;
		$callback($subConfigurator);
	}

	/**
	 * @param \Bitrix\Main\Lib\Solution|string $solutionClass
	 * @param $parameters
	 * @return void
	 */
	public function solution($solutionClass, $parameters = [])
	{
		/** @var Solution $solution */
		$solution = new $solutionClass;

		// set configuration to solution object
		if (is_array($parameters))
		{
			foreach ($parameters as $parameter => $value)
			{
				$solution->{$parameter} = $value;
			}
		}
		elseif ($parameters instanceof \Closure)
		{
			call_user_func($parameters, $solution);
		}

		// set solution object as route Option
		$this->options->solution($solution);

		// include routes
		$callbacks = $solution->getRoutes();

		foreach ($callbacks as $callback)
		{
			$subConfigurator = clone $this->configurator;
			$subConfigurator->mergeOptionsWith($this->options);

			$callback($subConfigurator);
		}
	}

	public function site($siteClass, $parameters = [])
	{
		$this->options->site($siteClass);

		/** @var Site $site */
		$site = new $siteClass;

		$callbacks = $site->getRoutes();

		foreach ($callbacks as $callback)
		{
			$subConfigurator = clone $this->configurator;
			$subConfigurator->mergeOptionsWith($this->options);

			$callback($subConfigurator);
		}
	}

	/**
	 * @deprecated
	 */
	public function bindOptions($scopeName, $optionsCallback)
	{
		$this->customOptions[$scopeName][] = $optionsCallback;

		return $this;
	}

	/**
	 * @deprecated
	 */
	public function bindRouteForComponent($scopeName, $fromRouteName, $toRouteName)
	{
		$this->bindOptions($scopeName, function (Options $options) use ($fromRouteName, $toRouteName) {
			$options->bindRoute($fromRouteName, $toRouteName);
		});

		return $this;
	}

	/**
	 * @deprecated
	 */
	public function bindSolutionForComponent($scopeName, $fromSolutionName, $toSolutionName)
	{
		$this->bindOptions($scopeName, function (Options $options) use ($fromSolutionName, $toSolutionName) {
			$options->bindSolution($fromSolutionName, $toSolutionName);
		});

		return $this;
	}

	public function release()
	{
		$routes = [];

		if ($this->routeContainer instanceof Route)
		{
			$route = $this->routeContainer;
			$route->setOptions($this->options);

			foreach ($this->customOptions as $scopeName => $optionsCallbacks)
			{
				$customOptions = new Options;

				foreach ($optionsCallbacks as $optionsCallback)
				{
					$optionsCallback($customOptions);
				}

				$route->addCustomOptions($scopeName, $customOptions);
			}

			$routes[] = $route;
		}

		return $routes;
	}
}