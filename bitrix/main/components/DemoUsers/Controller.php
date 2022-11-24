<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Components\DemoUsers;

/**
 * @package    bitrix
 * @subpackage main
 */
class Controller extends \Bitrix\Main\Lib\Controllers\Controller
{
	public function listAction()
	{
        $sections = [];
		return $this->render('default/template', compact('sections'));
	}
}
