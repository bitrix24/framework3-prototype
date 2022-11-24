<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Components\DemoNewsArticle;

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Model\NewsModel;

/**
 * @package    bitrix
 * @subpackage main
 */
class Controller extends \Bitrix\Main\Lib\Controllers\Controller
{
	public function detailAction()
	{
        $id = Context::getRoute()->getParameterValue('id');
        $article = NewsModel::getById($id);

		return $this->render('default/detail', compact('article'));
	}

    public function commentsAction()
	{
		return $this->render('default/comments', []);
	}
}
