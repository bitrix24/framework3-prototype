<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Cache\Adapters;

use Bitrix\Main\Lib\Cache\EmptyResult;
use Bitrix\Main\Lib\Cache\LockPolicy;
use Bitrix\Main\Lib\Type\ArrayHelper;

/**
 * @package    bitrix
 * @subpackage main
 */
class MemcachedAdapter extends AbstractAdapter implements LockableAdapterInterface
{
	/** @var \Memcache */
	protected $resource;

	protected $lockPolicy;

	public function __construct($options)
	{
		$this->resource = new \Memcache;

		$host = ArrayHelper::get($options, 'host', 'localhost');
		$port = ArrayHelper::get($options, 'port', '11211');
		$this->resource->addServer($host, $port);

		$this->lockPolicy = ArrayHelper::get($options, 'lockPolicy', LockPolicy::RETURN_OLD);
	}

	public function get($key)
	{
		$result = $this->resource->get($key);

		if ($result === false)
		{
			$locked = (bool) $this->resource->get('lock_' . $key);

			if ($locked)
			{
				// according to policy
				switch ($this->lockPolicy)
				{
					case LockPolicy::RETURN_EMPTY:
						return new EmptyResult;
					case LockPolicy::RETURN_OLD:
					case LockPolicy::WAIT:
						// try to read every second
						sleep(1);
						return $this->get($key);
				}
			}
			else
			{
				return new EmptyResult;
			}
		}

		return $result;
	}

	public function put($key, $value, $expiresAt)
	{
		$this->resource->set($key, $value, 0, $expiresAt - time());
	}

	public function delete($key)
	{
		$this->resource->delete($key);
	}

	public function deleteAll()
	{
		$this->resource->flush();
	}

	public function setLock($key)
	{
		$this->put('lock_' . $key, '1', time() + 3600);
	}

	public function releaseLock($key)
	{
		$this->resource->delete('lock_' . $key);
	}
}
