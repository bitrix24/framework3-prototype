<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Cache\Adapters;

/**
 * @package    bitrix
 * @subpackage main
 */
abstract class AbstractAdapter
{
	abstract public function get($key);

	abstract public function put($key, $value, $expiresAt);

	abstract public function delete($key);

	abstract public function deleteAll();
}
