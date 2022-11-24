<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Components\Menu;


/**
 * @package    bitrix
 * @subpackage main
 */
class Controller extends \Bitrix\Main\Lib\Controllers\Controller
{
	public function defaultAction()
	{
        $data = [
            'items' => [
                [
                    'DEPTH_LEVEL' => 1,
                    'SELECTED' => false,
                    'LINK' => '/something/1',
                    'TEXT' => 'Одежда',
                ],
                [
                    'DEPTH_LEVEL' => 1,
                    'SELECTED' => false,
                    'LINK' => '/something/2',
                    'TEXT' => 'Обувь',
                ],
                [
                    'DEPTH_LEVEL' => 1,
                    'SELECTED' => false,
                    'LINK' => '/something/3',
                    'TEXT' => 'Спортивный инвентарь',
                ]
            ],
            'parameters' => [
                'MAX_LEVEL' => 1
            ],
        ];

		return $this->render('default/template', $data);
	}
}
