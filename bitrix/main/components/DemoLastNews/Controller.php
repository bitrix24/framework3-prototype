<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Components\DemoLastNews;

use Bitrix\Main\Lib\Model\NewsModel;

/**
 * @package    bitrix
 * @subpackage main
 */
class Controller extends \Bitrix\Main\Lib\Controllers\Controller
{
    public function listAction()
    {
        $news = NewsModel::getLast();
        return $this->render('default/template', compact('news'));
    }
}
