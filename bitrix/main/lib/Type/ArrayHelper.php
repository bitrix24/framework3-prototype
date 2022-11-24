<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Type;

/**
 * Class description
 * @package    bitrix
 * @subpackage main
 */
class ArrayHelper
{
	public static function get($array, $key, $default)
	{
		return array_key_exists($key, $array)
			? $array[$key]
			: $default;
	}

	public static function hasIntKeys(array $array)
	{
		return count(array_filter(array_keys($array), 'is_int')) > 0;
	}
}
