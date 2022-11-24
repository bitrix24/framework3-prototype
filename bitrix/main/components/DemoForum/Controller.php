<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Components\DemoForum;

/**
 * @package    bitrix
 * @subpackage main
 */
class Controller extends \Bitrix\Main\Lib\Controllers\Controller
{
	public int $perPage = 10;

	public bool $showSectionDescription = true;

	public function mainAction()
	{
        $sections = [];
		return $this->render('default/template', compact('sections'));
	}
}
