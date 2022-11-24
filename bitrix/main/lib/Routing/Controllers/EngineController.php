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
use Bitrix\Main\Lib\Response;

/**
 * @package    bitrix
 * @subpackage main
 */
class EngineController extends Controller // TODO Engine?
{
	/** @var \Bitrix\Main\Lib\Engine\Controller */
	protected $controllerClass;

	/** @var string */
	protected $actionName;

	protected ControllerFactory $controllerFactory;

	/**
	 * EngineController constructor.
	 *
	 * @param $callback
	 * @param ControllerFactory $controllerFactory
	 */
	public function __construct($callback, ControllerFactory $controllerFactory)
	{
		[$this->controllerClass, $this->actionName] = $callback;

		$this->controllerFactory = $controllerFactory;
	}

	/**
	 * @return \Bitrix\Main\Lib\Engine\Controller
	 */
	public function getControllerClass()
	{
		return $this->controllerClass;
	}

	/**
	 * @return string
	 */
	public function getActionName()
	{
		return $this->actionName;
	}

	public function execute()
	{
		$site = Context::getSite();

		if (!empty($site))
		{
			// run in context of site
			$template = $site->getBlankPagePath();

			$context = [
				'controllerClass' => $this->controllerClass,
				'controllerAction' => $this->actionName
			];

			return (new Response($template, $context))->render();
		}
		else
		{
			$controller = $this->controllerFactory->create($this->controllerClass, route: $this->route);

			return Context::getContainer()->call(
				[$controller, $this->actionName],
				$this->route ? $this->route->getParametersValues()->getValues() : []
			);
		}
	}
}
