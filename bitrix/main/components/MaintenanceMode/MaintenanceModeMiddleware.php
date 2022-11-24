<?php

namespace Bitrix\Main\Components\MaintenanceMode;

use Bitrix\Main\Lib\Configuration\Configuration;
use Bitrix\Main\Lib\Controllers\ControllerFactory;
use Bitrix\Main\Lib\Middleware\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MaintenanceModeMiddleware extends Middleware
{
	protected Configuration $configuration;

	protected ControllerFactory $controllerFactory;

	public function __construct(Configuration $configuration, ControllerFactory $controllerFactory)
	{
		$this->configuration = $configuration;
		$this->controllerFactory = $controllerFactory;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if ($this->configuration->get('maintenance.mode'))
		{
			// call controller of this component
			$controller = $this->controllerFactory->create(MaintenanceModeController::class);

			return $controller->showMaintenanceMessage();
		}
		else
		{
			$response = $handler->handle($request);
		}

		return $response;
	}
}