<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Cache;

/**
 * @package    bitrix
 * @subpackage main
 */
class LockPolicy
{
	const WAIT = 1;

	/** @var int In case of no old value will WAIT */
	const RETURN_OLD = 2;

	const RETURN_EMPTY = 3;
}
