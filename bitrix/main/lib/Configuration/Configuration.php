<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Configuration;

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Module;
use Bitrix\Main\Lib\Type\ArrayHelper;

/**
 * @package    bitrix
 * @subpackage main
 */
class Configuration
{
	/** @var string */
	protected $configDir;

	public function __construct()
	{
		$this->configDir = PROJECT_ROOT.'/config';
	}

	/**
	 * Returns configuration value by key "config.key1.key2" or "module:config.key1.key2"
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		[$module, $configName, $parts] = static::parseKey($key);
		$configuration = $this->includeConfig($configName, $module);

		foreach ($parts as $part)
		{
			if (array_key_exists($part, $configuration))
			{
				$configuration = $configuration[$part];
			}
			else
			{
				return null;
			}
		}

		return  $configuration;
	}

	/**
	 * Returns merged configuration from site, site parent, project, module.
	 *
	 * @param ?Module $module
	 * @param string $configName
	 *
	 * @return array|RewriteValue|mixed
	 */
	protected function includeConfig(string $configName, ?Module $module = null)
	{
		$configurations = [];

		// module
		if (!empty($module))
		{
			$configPath = $module->getConfigPath().'/'.$configName.'.php';
			if (file_exists($configPath))
			{
				$configurations[] = include $configPath;
			}
		}

		// project
		$configPath = $this->configDir.'/';

		if (!empty($module))
		{
			$configPath .= $module->getName().'/';
		}

		$configPath .= $configName.'.php';

		if (file_exists($configPath))
		{
			$configurations[] = include $configPath;
		}

		$site = Context::getSite();

		// site and its parent
		if ($site)
		{
			$siteParent = $site->getParent();

			if ($siteParent)
			{
				$configPath = $siteParent->getConfigPath().'/'.$configName.'.php';
				if (file_exists($configPath))
				{
					$configurations[] = include $configPath;
				}
			}

			$configPath = $site->getConfigPath().'/'.$configName.'.php';
			if (file_exists($configPath))
			{
				$configurations[] = include $configPath;
			}
		}

		// merge
		return $this->merge($configurations);
	}

	/**
	 * Merges few configurations into one
	 *
	 * @param array $configurations
	 *
	 * @return array
	 */
	protected function merge(array $configurations)
	{
		$baseConfiguration = array_shift($configurations);

		if (!empty($configurations))
		{
			// merge each with base
			foreach ($configurations as $configuration)
			{
				$baseConfiguration = $this->mergeWithChild($baseConfiguration, $configuration);
			}
		}
		else
		{
			$baseConfiguration = $baseConfiguration ?? [];
		}

		return $baseConfiguration;
	}

	/**
	 * Merges two configurations.
	 *
	 * @param array|RewriteValue $baseConfiguration
	 * @param array|RewriteValue $childConfiguration
	 *
	 * @return array
	 */
	protected function mergeWithChild($baseConfiguration, $childConfiguration)
	{
		if ($childConfiguration instanceof RewriteValue)
		{
			return static::unpackRewriteValues($childConfiguration);
		}

		foreach ($childConfiguration as $key => $value)
		{
			if ( (is_array($value) && !ArrayHelper::hasIntKeys($value))
				|| $value instanceof RewriteValue
			)
			{
				// sub configuration
				if (isset($baseConfiguration[$key]))
				{
					$baseConfiguration[$key] = $this->mergeWithChild($baseConfiguration[$key], $value);
				}
				else
				{
					$baseConfiguration[$key] = $value instanceof RewriteValue
						? $value->getValues()
						: $value;
				}

			}
			else
			{
				// just a value
				$baseConfiguration[$key] = $value;
			}
		}

		return $baseConfiguration;
	}

	/**
	 * Recursively converts RewriteValue objects into arrays
	 *
	 * @param array|RewriteValue $value
	 *
	 * @return array
	 */
	public static function unpackRewriteValues($value)
	{
		$value = $value instanceof RewriteValue
			? $value->getValues()
			: $value;

		foreach ($value as $k => $v)
		{
			if (is_array($v) || $v instanceof RewriteValue)
			{
				$value[$k] = static::unpackRewriteValues($v);
			}
		}

		return $value;
	}

	/**
	 * Receives key "config.key1.key2" or "module:config.key1.key2"
	 * and returns [Module $module, string $configName, array $parts]
	 * where $parts would be ["key1", "key2"]
	 *
	 * @param $key
	 *
	 * @return array|Module[]
	 */
	public static function parseKey($key)
	{
		// parse module
		$colonPos = strpos($key, ':');

		if ($colonPos)
		{
			$moduleFqn = substr($key, 0, $colonPos);
			$configKey = substr($key, $colonPos + 1);

			$module = Module::createFromString($moduleFqn);
		}
		else
		{
			$module = null;
			$configKey = $key;
		}

		// parse config key
		$parts = explode('.', $configKey);
		$configName = array_shift($parts);

		return [$module, $configName, $parts];
	}
}
