<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Components\JsCore;

use Bitrix\Main\Lib\ExtensionLoader;

/**
 * @package    bitrix
 * @subpackage main
 */
class Configuration
{
	public function getDefaultAction()
	{
		return function()
		{
			ExtensionLoader::load(Extension::class);
		};
	}
}
