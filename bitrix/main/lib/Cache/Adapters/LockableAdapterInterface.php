<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */


namespace Bitrix\Main\Lib\Cache\Adapters;


interface LockableAdapterInterface
{
	public function setLock($key);

	public function releaseLock($key);
}