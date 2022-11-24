<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Cache;

use Bitrix\Main\Lib\Cache\Adapters\AbstractAdapter;
use Bitrix\Main\Lib\Cache\Adapters\LockableAdapterInterface;

/**
 * @package    bitrix
 * @subpackage main
 */
class Pool implements \Psr\Cache\CacheItemPoolInterface
{
	/** @var AbstractAdapter */
	protected $adapter;

	/** @var Item[] */
	protected $items;

	/** @var Item[] */
	protected $itemsToSave;

	public function __construct(AbstractAdapter $adapter)
	{
		$this->adapter = $adapter;
	}

	public function getItem($key)
	{
		// get from internal pool
		if (isset($this->items[$key]))
		{
			return $this->items[$key];
		}

		// get from adapter
		$isHit = true;
		$value = $this->adapter->get($key);

		if ($value instanceof EmptyResult)
		{
			$isHit = false;
			$value = null;
		}

		$item = new Item($key, $value, $isHit);
		$this->items[$key] = $item;

		return $item;
	}

	public function getItems(array $keys = array())
	{
		$items = [];

		foreach ($keys as $key)
		{
			$items[] = $this->getItem($key);
		}

		return $items;

		// TODO: Implement MultiGetAdapterInterface
	}

	public function hasItem($key)
	{
		$item = $this->getItem($key);

		return $item->isHit();

		// TODO: Implement HasItemAdapterInterface
	}

	public function clear()
	{
		$this->adapter->deleteAll();
	}

	public function deleteItem($key)
	{
		$this->adapter->delete($key);
		unset($this->items[$key]);
	}

	public function deleteItems(array $keys)
	{
		foreach ($keys as $key)
		{
			$this->deleteItem($key);
		}

		// TODO: Implement MultiDeleteAdapterInterface
	}

	public function setLock($key)
	{
		$this->adapter->setLock($key);
	}

	public function save(\Psr\Cache\CacheItemInterface $item)
	{
		/** @var $item Item */
		$result = $this->adapter->put(
			$item->getKey(),
			$item->get(),
			$item->getExpiresAt()
		);

		// unlock
		if ($this->adapter instanceof LockableAdapterInterface)
		{
			$this->adapter->releaseLock($item->getKey());
		}

		// actualize pool
		$item->setIsHit(true);
		$this->items[$item->getKey()] = $item;
	}

	public function saveDeferred(\Psr\Cache\CacheItemInterface $item)
	{
		/** @var $item Item */
		$this->itemsToSave[$item->getKey()] = $item;

		// actualize pool
		$item->setIsHit(true);
		$this->items[$item->getKey()] = $item;
	}

	public function commit()
	{
		foreach ($this->itemsToSave as $item)
		{
			$this->save($item);
		}

		$this->itemsToSave = [];
	}

	/**
	 * @return AbstractAdapter
	 */
	public function getAdapter(): AbstractAdapter
	{
		return $this->adapter;
	}
}
