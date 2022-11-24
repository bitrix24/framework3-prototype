<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing;

use Bitrix\Main\Lib\Middleware\Middleware;
use Bitrix\Main\Lib\QueueRequestHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @package    bitrix
 * @subpackage main
 */
class RouteMatchMiddleware extends Middleware
{
	/** @var Router */
	protected $router;

	/**
	 * RouteMatchMiddleware constructor.
	 *
	 * @param Router $router
	 */
	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	public function process(ServerRequestInterface  $request, RequestHandlerInterface $handler) : ResponseInterface
	{
		/** @var QueueRequestHandler $handler */
		$this->router->match($request, $handler);

		return $handler->handle($request);
	}
}
