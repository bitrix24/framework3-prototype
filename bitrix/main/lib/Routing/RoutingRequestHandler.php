<?php

namespace Bitrix\Main\Lib\Routing;

use Bitrix\Main\Lib\Routing\Controllers\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingRequestHandler implements RequestHandlerInterface
{
	protected Controller $controller;

	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
	}

	public function handle(ServerRequestInterface  $request) : ResponseInterface
	{
		return ($this->controller)();
	}
}