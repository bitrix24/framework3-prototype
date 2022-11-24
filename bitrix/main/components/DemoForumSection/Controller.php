<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Components\DemoForumSection;

/**
 * @package    bitrix
 * @subpackage main
 */
class Controller extends \Bitrix\Main\Lib\Controllers\Controller
{
	public function mainAction()
	{
        $threads = $this->prepareThreads();
		return $this->render('default/template', compact('threads'));
	}

    protected function prepareThreads()
    {
        return [1];
    }
}
