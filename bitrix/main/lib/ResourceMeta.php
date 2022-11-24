<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib;

/**
 * @package    bitrix
 * @subpackage main
 */
class ResourceMeta
{
	/** @var string */
	protected $path;

	/** @var array [path => hash] */
	protected $map;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function getHash($resourcePath)
	{
		$hash = null;

		if (!empty($this->getMap()[strtolower($resourcePath)]))
		{
			$hash = $this->getMap()[strtolower($resourcePath)];
		}

		return $hash;
	}

	public function getMap()
	{
		if ($this->map === null)
		{
			$lines = array_filter(file($this->path));

			foreach ($lines as $line)
			{
				[$path, $hash] = explode(':', trim($line));
				$this->map[strtolower($path)] = $hash;
			}
		}

		return $this->map;
	}
}
