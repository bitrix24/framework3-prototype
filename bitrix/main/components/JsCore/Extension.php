<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Components\JsCore;

/**
 * @package    bitrix
 * @subpackage main
 */
class Extension extends \Bitrix\Main\Lib\Controllers\Extension
{
	public function getJs()
	{
        return [
            'resources/js/core.js'
        ];
	}
}
