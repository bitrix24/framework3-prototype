<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteRequestHandler implements RequestHandlerInterface
{
	protected Route $route;

	public function __construct($route)
	{
		$this->route = $route;
	}

	public function handle(ServerRequestInterface  $request) : ResponseInterface
	{
		// call the controller
		$controller = $this->route->getController();

		return $controller();
	}

	/**
	 * @return Route
	 */
	public function getRoute(): Route
	{
		return $this->route;
	}
}