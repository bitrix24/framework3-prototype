<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig;

use Bitrix\Main\Lib\Context;
use Twig\Cache\FilesystemCache;
use Twig\Environment;

/**
 * @package    bitrix
 * @subpackage main
 */
class FileCache extends FilesystemCache
{
	protected $directory;
	protected $env;

	public function __construct(string $directory, int $options = 0, Environment $env = null)
	{
		parent::__construct($directory, $options);
		$this->env = $env;

		// duplicate private property
		$this->directory = $directory;
	}

	public function generateKey(string $name, string $className): string
	{
		$fullPath = $this->env->getLoader()->getCacheKey($name);
		$relativePath = $fullPath;

		$projectRoot = Context::getCurrent()->getServer()->getDocumentRoot();
		if (strpos($fullPath, $projectRoot) === 0)
		{
			$relativePath = substr($fullPath, strlen($projectRoot));
		}

		return $this->directory.'/'.$relativePath.'.php';
	}
}
