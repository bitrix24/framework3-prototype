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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @package    bitrix
 * @subpackage main
 */
class FilesystemAdapter extends AbstractAdapter implements LockableAdapterInterface
{
	/** @var string */
	protected $dir;

	protected $lockPolicy;

	protected $currentlyLocked = [];

	public function __construct($options)
	{
		$this->dir = PROJECT_ROOT . '/' . $options['dir'];
		$this->lockPolicy = ArrayHelper::get($options, 'lockPolicy', LockPolicy::RETURN_OLD);
	}

	public function get($key)
	{
		$hash = static::getKeyHash($key);
		$cacheDir = $this->getSubdirByHash($hash);
		$cacheFilePath = "{$cacheDir}/{$hash}";

		if (!file_exists($cacheFilePath))
		{
			return new EmptyResult;
		}

		// read data
		$cacheContent = file_get_contents($cacheFilePath);
		$cacheData = $this->unpackData($cacheContent);

		if (!empty($cacheData) && time() <= $cacheData['exp'])
		{
			// return valid item
			return $cacheData['v'];
		}

		// check locking
		$cacheFile = fopen($cacheFilePath, 'r');

		$locked = $this->isFileLocked($cacheFile);
		$waitLockRelease = false;

		if (!empty($cacheData) && time() > $cacheData['exp'])
		{
			// handle expired record
			if ($locked)
			{
				// according to policy
				switch ($this->lockPolicy)
				{
					case LockPolicy::RETURN_EMPTY:
						return new EmptyResult;
					case LockPolicy::RETURN_OLD:
						return $cacheData['v'];
					case LockPolicy::WAIT:
						// wait
						$waitLockRelease = true;
						break;
				}
			}
			else
			{
				// remove expired row
				$this->delete($key);

				return new EmptyResult;
			}
		}
		else
		{
			// handle empty file
			if ($locked)
			{
				// wait for result
				$waitLockRelease = true;
			}
			else
			{
				// invalid value
				$this->delete($key);

				return new EmptyResult;
			}
		}

		if ($waitLockRelease)
		{
			// try to read every second
			while (true)
			{
				sleep(1);

				if (!flock($cacheFile, LOCK_EX | LOCK_NB, $wouldBlock))
				{
					if ($wouldBlock)
					{
						// another process holds the lock
						continue;
					}
				}

				// lock obtained. release now
				flock($cacheFile, LOCK_UN);

				return $this->get($key);
			}
		}
	}

	public function put($key, $value, $expiresAt)
	{
		$hash = static::getKeyHash($key);
		$cacheDir = $this->getSubdirByHash($hash);
		$cacheFilePath = "{$cacheDir}/{$hash}";

		if (!file_exists($cacheDir))
		{
			mkdir($cacheDir, 0777, true);
		}

		$cacheData = [
			'exp' => $expiresAt,
			'v' => $value
		];

		$cacheContent = $this->packData($cacheData);

		return (bool) file_put_contents($cacheFilePath, $cacheContent);
	}

	public function delete($key)
	{
		$hash = static::getKeyHash($key);
		$cacheDir = $this->getSubdirByHash($hash);
		$cacheFilePath = "{$cacheDir}/{$hash}";

		unlink($cacheFilePath);
	}

	public function deleteAll()
	{
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($this->dir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($files as $fileInfo)
		{
			$todo = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
			$todo($fileInfo->getRealPath());
		}
	}

	public function setLock($key)
	{
		$hash = static::getKeyHash($key);
		$cacheDir = $this->getSubdirByHash($hash);
		$cacheFilePath = "{$cacheDir}/{$hash}";

		// create empty file
		file_put_contents($cacheFilePath, '');

		// lock it
		$cacheFile = fopen($cacheFilePath, 'r');
		flock($cacheFile, LOCK_EX | LOCK_NB, $wouldBlock);

		// save resource for unlock
		$this->currentlyLocked[$key] = $cacheFile;
	}

	public function releaseLock($key)
	{
		if (empty($this->currentlyLocked[$key]))
		{
			return;
		}

		$cacheFile = $this->currentlyLocked[$key];
		flock($cacheFile, LOCK_UN);
		fclose($cacheFile);
	}

	protected function isFileLocked($cacheFile)
	{
		$locked = false;

		if (!flock($cacheFile, LOCK_EX | LOCK_NB, $wouldBlock))
		{
			if ($wouldBlock)
			{
				// another process holds the lock
				$locked = true;
			}
		}
		else
		{
			// lock obtained. release now
			flock($cacheFile, LOCK_UN);
		}

		return $locked;
	}

	public function getSubdirByHash($hash)
	{
		$subdir = substr($hash, -3);

		return "{$this->dir}/{$subdir}";
	}

	public function packData($data)
	{
		return serialize($data);
	}

	public function unpackData($content)
	{
		return unserialize($content);
	}

	public static function getKeyHash($key)
	{
		return md5($key);
	}
}
