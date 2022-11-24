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
class Module
{
	/** @var string */
	protected $name;

	/** @var string */
	protected $vendor;

	const DEFAULT_VENDOR = 'bitrix';

	/**
	 * Module constructor.
	 *
	 * @param string $name
	 * @param string $vendor
	 */
	public function __construct($name, $vendor = null)
	{
		$this->name = $name;
		$this->vendor = $vendor ?? static::DEFAULT_VENDOR;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getVendor(): string
	{
		return $this->vendor;
	}

	public function getConfigPath()
	{
		return $this->getSourcePath().'/config';
	}

	public function getSourcePath()
	{
		$path = PROJECT_ROOT.'/';

		if ($this->vendor === static::DEFAULT_VENDOR)
		{
			$path .= 'bitrix/'.$this->name;
		}
		else
		{
			$path .= 'modules/'.$this->vendor.'.'.$this->name;
		}

		return $path;
	}

	public static function createFromString($module)
	{
		if (strpos($module, '.'))
		{
			list($vendor, $name) = explode('.', $module);
		}
		else
		{
			$name = $module;
			$vendor = static::DEFAULT_VENDOR;
		}

		return new static($name, $vendor);
	}
}
