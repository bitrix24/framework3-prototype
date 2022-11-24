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
class Item implements \Psr\Cache\CacheItemInterface
{
	protected $key;

	protected $value;

	protected $isHit;

	protected $expire;

	/**
	 * Item constructor.
	 *
	 * @param $key
	 * @param $value
	 * @param $isHit
	 */
	public function __construct($key, $value, $isHit)
	{
		$this->key = $key;
		$this->value = $value;
		$this->isHit = (bool) $isHit;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function get()
	{
		return $this->value;
	}

	public function isHit()
	{
		return $this->isHit;
	}

	public function set($value)
	{
		$this->value = $value;
	}

	public function expiresAt($expiration)
	{
		$this->expire = $expiration;
	}

	public function expiresAfter($time)
	{
		$this->expire = time() + $time;
	}

	public function getExpiresAt()
	{
		return $this->expire;
	}

	public function setIsHit($isHit = true)
	{
		$this->isHit = (bool) $isHit;
	}
}
