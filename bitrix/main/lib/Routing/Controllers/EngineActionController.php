<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing\Controllers;

use Bitrix\Main\Lib\Engine\Action;
use Bitrix\Main\Lib\Engine\Contract\RoutableAction;
use Bitrix\Main\Lib\Loader;
use Bitrix\Main\Lib\SystemException;

/**
 * @package    bitrix
 * @subpackage main
 */
class EngineActionController extends Controller
{
	/** @var Action|string */
	protected $actionClass;

	/**
	 * @param Action|string $actionClass
	 */
	public function __construct($actionClass)
	{
		$this->actionClass = $actionClass;
	}

	public function execute()
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

		// actually action could be attached to a few controllers
		// but what if action was made for the one controller only
		// then it could be used in routing
		Loader::requireClass($this->actionClass);

		if (is_subclass_of($this->actionClass, Action::class))
		{
			if (is_subclass_of($this->actionClass, RoutableAction::class))
			{
				/** @var RoutableAction $actionClass */
				$controllerClass = $this->actionClass::getControllerClass();
				$actionName = $this->actionClass::getDefaultName();

				/** @var \Bitrix\Main\Lib\HttpApplication $app */
				$app = \Bitrix\Main\Lib\Application::getInstance();
				$app->runController($controllerClass, $actionName);
			}
			else
			{
				throw new SystemException(sprintf(
					'Action `%s` should implement %s interface for being called in routing',
					$this->actionClass, RoutableAction::class
				));
			}
		}
		else
		{
			throw new SystemException(sprintf(
				'Unknown controller `%s`', gettype($this->actionClass)
			));
		}
	}
}
