<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Cache;

use Bitrix\Main\Lib\Cache\Adapters\LockableAdapterInterface;
use Bitrix\Main\Lib\Context;

/**
 * @package    bitrix
 * @subpackage main
 */
class Cache
{
	public function get($key, $valueCallback)
	{
		$cache = Context::getCache();
		$item = $cache->getItem($key);

		// return if got cached value
		if ($item->isHit())
		{
			return $item->get();
		}

		// set global lock
		if ($cache->getAdapter() instanceof LockableAdapterInterface)
		{
			$cache->setLock($key);
		}

		// get value and cache it
		$value = call_user_func($valueCallback, $item);

		if ($value instanceof EmptyResult)
		{
			return null;
		}

		$item->set($value);
		$cache->save($item);

		return $value;
	}
}
