<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing;

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\QueueRequestHandler;
use Bitrix\Main\Lib\Routing\Controllers\Controller;
use Bitrix\Main\Lib\Routing\Exceptions\ParameterNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package    bitrix
 * @subpackage main
 */
class Router
{
	/** @var Route[] */
	protected $routes = [];

	/** @var Route[] */
	protected $routesByName = [];

	/** @var RoutingConfiguration[] */
	protected $configurations = [];

	/** @var Controller */
	protected $fallbackNotFound;

	public function registerConfiguration($configuration)
	{
		$this->configurations[] = $configuration;
	}

	public function releaseRoutes()
	{
		// go recursively through routes tree
		$i = -1;
		while (isset($this->configurations[++$i]))
		{
			$this->routes = array_merge($this->routes, $this->configurations[$i]->release());
		}

		// reindex
		$this->reindexRoutes();

		// don't need them anymore
		$this->configurations = [];
	}

	protected function reindexRoutes()
	{
		$this->routesByName = [];

		foreach ($this->routes as $route)
		{
			if ($route->getOptions() && $route->getOptions()->hasName())
			{
				$this->routesByName[$route->getOptions()->getFullName()] = $route;
			}
		}
	}

	/**
	 * @param ServerRequestInterface $request
	 *
	 * @param QueueRequestHandler $requestHandler TODO should be RequestHandlerInterface interface
	 *
	 * @return void
	 */
	public function match($request, $requestHandler)
	{
		$path = urldecode($request->getUri()->getPath());

		foreach ($this->routes as $route)
		{
			if ($matchResult = $route->match($path, $request, $requestHandler))
			{
				// check for routing tree match
				if ($requestHandler->getFallbackHandler() instanceof RouteRequestHandler)
				{
					break;
				}

				// check method
				if (!empty($route->getOptions()->getMethods())
					&& !in_array($request->getMethod(), $route->getOptions()->getMethods(), true))
				{
					continue;
				}

				// set route parameters
				if (is_array($matchResult))
				{
					$route->getParametersValues()->setValues($matchResult);
				}

				// set site
				if ($route->getOptions()->hasSite())
				{
					$siteClass = $route->getOptions()->getSite();
					$site = new $siteClass;

					// set layout
					if ($route->getOptions()->hasLayout())
					{
						$site->setLayout($route->getOptions()->getLayout());
					}

					Context::setSite($site);
				}

				// set current route
				Context::setRoute($route);

				//$fallbackHandler = new RouteRequestHandler($route);
				$fallbackHandler = new RoutingRequestHandler($route->getController());
				$requestHandler->setFallbackHandler($fallbackHandler);

				// add route specific middleware
				if ($route->getOptions()->getMiddleware())
				{
					foreach ($route->getOptions()->getMiddleware() as $middlewareClass)
					{
						$requestHandler->addMiddleware(new $middlewareClass);
					}
				}

				break;
			}
		}

		// not found
		if (empty($requestHandler->getFallbackHandler()) && isset($this->fallbackNotFound))
		{
			$fallbackHandler = new RoutingRequestHandler($this->fallbackNotFound);
			$requestHandler->setFallbackHandler($fallbackHandler);
		}
	}

	public function url($url, $parameters = [])
	{
		// scheme, domain?
		$finalUrl = $url;

		if (!empty($parameters))
		{
			$finalUrl .= '?'.http_build_query($parameters);
		}

		return $finalUrl;
	}

	public function route($name, $parameters = [])
	{
		if (!empty($this->routesByName[$name]))
		{
			// route should be compiled
			$route = $this->routesByName[$name];
			$route->compile();

			$uri = $route->getUri();

			if (!empty($routeParameters = $route->getParameters()))
			{
				foreach ($routeParameters as $parameterName => $pattern)
				{
					if (array_key_exists($parameterName, $parameters))
					{
						// get from user
						$value = $parameters[$parameterName];

						// remove from user list
						unset($parameters[$parameterName]);
					}
					elseif ($route->getOptions() && $route->getOptions()->hasDefault($parameterName))
					{
						$value = $route->getOptions()->getDefault($parameterName);
					}
					elseif ($route->getOptions() && $route->getOptions()->hasBoundParameter($parameterName))
					{
						$value = $route->getOptions()->getBoundParameter($parameterName, $routeParameters);
					}
					else
					{
						throw new ParameterNotFoundException(sprintf(
							'Parameter `%s` was not found', $parameterName
						));
					}

					// check with pattern?

					$uri = str_replace("{{$parameterName}}", urlencode($value), $uri);
				}
			}

			// additional parameters as query string
			if (!empty($parameters))
			{
				$uri .= '?'.http_build_query($parameters);
			}

			return $uri;
		}
	}

	/**
	 * @return Route[]
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	public function hasRouteByName($routeName)
	{
		return !empty($this->routesByName[$routeName]);
	}

	public function setFallbackNotFound(Controller $controller)
	{
		$this->fallbackNotFound = $controller;
	}

	public function getFallbackNotFound()
	{
		return $this->fallbackNotFound;
	}
}