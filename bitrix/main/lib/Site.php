<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib;

use Bitrix\Main\Lib\Exceptions\SystemException;
use Bitrix\Main\Lib\Routing\RoutablePackageTrait;
use Bitrix\Main\Lib\Twig\Stack\Extension\StackExtension;

/**
 * @package    bitrix
 * @subpackage main
 */
abstract class Site
{
	use RoutablePackageTrait {
		getRoutes as protected baseGetRoutes;
	}

	/** @var ?self */
	protected ?self $parent = null;

	/** @var class-string<Layout> */
	protected $layout;

	public function getConfigPath()
	{
		return $this->getPath().'/config';
	}

	public function getPagePath($pagePath)
	{
		$path = $this->getPath() . '/pages/' . $pagePath;

		if (!file_exists($path) && $this->getParent())
		{
			$path = $this->getParent()->getPagePath($pagePath);
		}

		return $path;
	}

	public function getBlankPagePath()
	{
		return $this->getPagePath('blank.twig');
	}

	/**
	 * @return static
	 */
	public function getParent()
	{
		if ($this->parent === null)
		{
			$refClass = new \ReflectionClass($this);

			if ($refClass->getParentClass()->getName() !== __CLASS__)
			{
				$this->parent = $refClass->getParentClass()->newInstance();
			}
		}

		return $this->parent;
	}

	public function getLayout()
	{
		return $this->layout;
	}

	public function setLayout($name)
	{
		$this->layout = $name;
	}

	/**
	 * @internal
	 * For internal usage in Twig. Adds layout context as global parameters
	 *
	 * @param class-string<Layout> $layoutClass
	 * @return string
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function loadLayout($layoutClass = null)
	{
		$layoutClass = $layoutClass ?: $this->layout;

		if (!isset($layoutClass))
		{
			throw new SystemException(sprintf(
				'Undefined layout for `%s` site', get_class($this)
			));
		}

		/** @var Layout $layout */
		$layout = Context::getContainer()->make($layoutClass);
		$globals = $layout->getData();

		// load global parameters
		if (!empty($globals))
		{
			$env = Response::getTwigEnvironment();
			$ext = $env->getExtension(StackExtension::class);

			foreach ($globals as $key => $value)
			{
				$ext->addGlobal($key, $value);
			}
		}

		return $layout->getFileProjectPath();
	}

	public function getRoutes()
	{
		$routeCallbacks = $this->baseGetRoutes();

		// get parent
		if ($this->getParent())
		{
			$routeCallbacks = array_merge($routeCallbacks, $this->getParent()->getRoutes());
		}

		return $routeCallbacks;
	}
}
