<?php

namespace Bitrix\Main\Lib\Exceptions;

use Bitrix\Main\Components\ErrorPages\ErrorPagesController;
use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Routing\Controllers\Controller;
use Bitrix\Main\Lib\Routing\Controllers\RouteControllerFactory;

class ErrorResponseException extends \Exception
{
	protected ?Controller $controller = null;

	public function __construct($message = "", $code = 503, $controller = null, $previous = null)
	{
		if (isset($controller))
		{
			$this->controller = Context::getContainer()->get(RouteControllerFactory::class)->create($controller);
		}
		else
		{
			// default value according to code
			if (!in_array($code, ErrorPagesController::$codes))
			{
				$code = 503;
			}

			$actionName = "show{$code}Error";
			$this->controller = Context::getContainer()->get(RouteControllerFactory::class)->create([ErrorPagesController::class, $actionName]);

			// reset site context
			Context::unsetSite();
		}

		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return mixed
	 */
	public function getController(): ?Controller
	{
		if (!isset($this->controller))
		{
			$code = $this->code;

			// default value according to code
			if (!in_array($code, ErrorPagesController::$codes))
			{
				$code = 503;
			}

			$actionName = "show{$code}Error";
			$this->controller = Context::getContainer()->get(RouteControllerFactory::class)->create([ErrorPagesController::class, $actionName]);

			// reset site context
			Context::unsetSite();
		}

		return $this->controller;
	}

	/**
	 * @param mixed $controller
	 */
	public function setController(Controller $controller): void
	{
		$this->controller = $controller;
	}
}