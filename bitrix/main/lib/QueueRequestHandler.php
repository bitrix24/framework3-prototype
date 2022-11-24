<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @package    bitrix
 * @subpackage main
 */
class QueueRequestHandler implements RequestHandlerInterface
{
	/** @var MiddlewareInterface[] */
	protected array $middleware = [];

	protected ?RequestHandlerInterface $fallbackHandler = null;

	public function addMiddleware(MiddlewareInterface  $middleware)
	{
		$this->middleware[] = $middleware;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		if (0 === count($this->middleware))
		{
			// last middleware in the queue has called on the request handler
			return $this->fallbackHandler->handle($request);
		}

		$middleware = array_shift($this->middleware);

		return $middleware->process($request, $this);
	}

	public function setFallbackHandler(RequestHandlerInterface  $fallbackHandler): void
	{
		$this->fallbackHandler = $fallbackHandler;
	}

	public function getFallbackHandler() : ?RequestHandlerInterface
	{
		return $this->fallbackHandler;
	}
}
