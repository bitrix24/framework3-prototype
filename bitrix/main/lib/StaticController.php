<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib;

use Bitrix\Main\Lib\Controllers\Controller;

/**
 * @package    bitrix
 * @subpackage main
 */
class StaticController extends Controller
{
	public function resolveAction()
	{
		$route = Context::getRoute();
		$path = $route->getParameterValue('path');
		$filePath = $path;

		// try to extract hash
		$lastDotPos = strrpos($path, '.');
		$prevDotPos = strrpos(substr($path, 0, $lastDotPos), '.');
		$hash = substr($path, $prevDotPos + 1, ($lastDotPos - $prevDotPos) - 1);
		$ext = substr($path, $lastDotPos + 1);

		if (preg_match('/^[a-f0-9]{12}$/', $hash))
		{
			$filePath = substr($path, 0, $prevDotPos) . substr($path, $lastDotPos);
		}

		// TODO: check hash validity to prevent false positive copies of file

		$filePath = PROJECT_ROOT.'/'.$filePath;
		$fileContent = file_get_contents($filePath);

		if ($ext === 'css')
		{
			header('Content-Type: text/css');

			// if css, then replace paths to pics
			// only for absolute paths, as long as relatives already works
			$fileContent = $this->replaceCssImagePaths($fileContent, $filePath);
		}

		// save public cache
		$publicPath = PROJECT_ROOT.'/public/resources/'.$path;
		$publicDir = dirname($publicPath);

		if (!file_exists($publicDir))
		{
			mkdir($publicDir, 0777, true);
		}

		file_put_contents($publicPath, $fileContent);

		// output or internal redirect
		echo $fileContent;
		die;
	}

	protected function replaceCssImagePaths($cssContent, $cssFilePath)
	{
		$cssParser = new \Sabberworm\CSS\Parser($cssContent);
		$cssDocument = $cssParser->parse();

		foreach($cssDocument->getAllValues() as $cssValue)
		{
			if($cssValue instanceof \Sabberworm\CSS\Value\URL)
			{
				$url = $cssValue->getURL()->getString();

				// skip absolute urls with host
				if (strpos($url,'://') !== false)
				{
					continue;
				}

				// transform url
				$absPath = static::isRelativePath($url)
					? dirname($cssFilePath).'/'.$url
					: PROJECT_ROOT.$url;

				$realPath = realpath($absPath);

				if ($realPath === false)
				{
					trigger_error(sprintf('File `%s` does not exist ', $absPath), E_USER_WARNING);
				}
				else
				{
					// file exists
					$hash = substr(md5_file($realPath), 0, 12);
					$relativePath = str_replace(PROJECT_ROOT.'/', '', $realPath);

					//
					$publicUrl = static::rewriteFileNameWithHash('/resources/'.$relativePath, $hash);
					$cssValue->getURL()->setString($publicUrl);

					// save to global map
					$map[$relativePath] = $hash;
				}
			}
		}

		return $cssDocument->render();
	}

	protected static function isRelativePath($path)
	{
		if (strpos($path,'://') !== false)
		{
			return false;
		}
		elseif (substr($path,0,1) === '/')
		{
			return false;
		}
		else
		{
			// no protocol and no leading slash: relative
			return true;
		}
	}

	protected static function rewriteFileNameWithHash($path, $hash)
	{
		$resourcePathParts = explode('/', $path);

		$resourceFilename = basename($path);

		$lastDotPos = strrpos($resourceFilename, '.');
		$resourceName = substr($resourceFilename, 0, $lastDotPos);
		$resourceExt = substr($resourceFilename, $lastDotPos + 1);

		$staticResourceFilename = $resourceName.'.'.$hash.'.'.$resourceExt;
		$resourcePathParts[count($resourcePathParts)-1] = $staticResourceFilename;

		// return uri with hash
		$path = join('/', $resourcePathParts);

		return $path;
	}

}
