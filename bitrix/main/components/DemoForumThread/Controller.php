<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Components\DemoForumThread;

/**
 * @package    bitrix
 * @subpackage main
 */
class Controller extends \Bitrix\Main\Lib\Controllers\Controller
{
	public $perPage = 10;

	public function mainAction()
	{
		return $this->render('default/template', [
			'perPage' => $this->perPage
		]);
	}

    public function messageAction()
    {
        $sections = [];
        return $this->render('default/message', compact('sections'));
    }
}
