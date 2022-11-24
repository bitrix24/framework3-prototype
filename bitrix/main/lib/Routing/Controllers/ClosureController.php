<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing\Controllers;

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Controllers\ControllerFactory;

/**
 * @package    bitrix
 * @subpackage main
 */
class ClosureController extends Controller
{
	protected \Closure $closure;

	protected ControllerFactory $controllerFactory;

	/**
	 * @param \Closure $closure
	 * @param ControllerFactory $controllerFactory
	 */
	public function __construct(\Closure $closure, ControllerFactory $controllerFactory,)
	{
		$this->closure = $closure;
		$this->controllerFactory = $controllerFactory;
	}

	public function execute()
	{
		$container = Context::getContainer();

		$routeParameters = $this->route
			? $this->route->getParametersValues()->getValues()
			: [];

		// if signature with controller
		$reflection = new \ReflectionFunction($this->closure);

		foreach ($reflection->getParameters() as $reflectionParameter)
		{
			if (is_subclass_of($reflectionParameter->getType()?->getName(), \Bitrix\Main\Lib\Controllers\Controller::class))
			{
				$controllerParameterName = $reflectionParameter->getName();
				$controllerClass = $reflectionParameter->getType()->getName();
				break;
			}
		}

		if (!empty($controllerParameterName) && !empty($controllerClass))
		{
			// init controller
			$controller = $this->controllerFactory->create($controllerClass, route: $this->route);

			// set as parameter
			$routeParameters[$controllerParameterName] = $controller;
		}

		return $container->call($this->closure, $routeParameters);
	}
}
