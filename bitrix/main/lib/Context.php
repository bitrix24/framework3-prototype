<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib;

use Bitrix\Main\Lib\Cache\Pool;
use Bitrix\Main\Lib\Configuration\Configuration;
use Bitrix\Main\Lib\Routing\Route;
use Bitrix\Main\Lib\Routing\Router;
use Bitrix\Main\Lib\Twig\Stack\Extension\StackExtension;
use DI\Container;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Whoops\Run;

/**
 * @package    bitrix
 * @subpackage main
 */
class Context
{
	/** @var Kernel */
	protected static $kernel;

	protected static Container $container;

	/** @var Configuration */
	protected static $configuration;

	/** @var Site */
	protected static $site;

	/** @var AssetManager */
	protected static $assetManager;

	/** @var EventManager */
	protected static $eventManager;

	/** @var Route */
	protected static $route = null;

	/** @var Router */
	protected static $router;

	/** @var Pool[] */
	protected static $cachePools;

	public static function setKernel($kernel)
	{
		static::$kernel = $kernel;
	}

	public static function getContainer()
	{
		if (!isset(static::$container))
		{
			$builder = new \DI\ContainerBuilder();

			$definitions = [PROJECT_ROOT.'/config/container/base.php']; // TODO include all dir

			foreach ($definitions as $definition)
			{
				$builder->addDefinitions($definition);
			}

			static::$container = $builder->build();
		}

		return static::$container;
	}

	public static function getConfiguration()
	{
		if (static::$configuration === null)
		{
			static::$configuration = new Configuration;
		}

		return static::$configuration;
	}

	public static function setSite(Site $site)
	{
		static::$site = $site;
	}

	/**
	 * @return Site
	 */
	public static function getSite()
	{
		return static::$site;
	}

	public static function unsetSite()
	{
		static::$site = null;
	}

	/**
	 * @return AssetManager
	 */
	public static function getAssetManager()
	{
		if (static::$assetManager === null)
		{
			$assetManager = new AssetManager;

			static::getEventManager()->addEventHandler(
				'main',
				'onPageRender',
				function (Event $event) use ($assetManager) {
					/** @var StackExtension $ext */
					$ext = $event->getParameter(StackExtension::class);
					$ext->pushStack('header', $assetManager->render(), true);
				}
			);

			static::$assetManager = $assetManager;
		}

		return static::$assetManager;
	}

	/**
	 * @param AssetManager $assetManager
	 */
	public static function setAssetManager($assetManager)
	{
		static::$assetManager = $assetManager;
	}

	/**
	 * @return EventManager
	 */
	public static function getEventManager()
	{
		if (static::$eventManager === null)
		{
			static::$eventManager = EventManager::getInstance();
		}

		return self::$eventManager;
	}

	/**
	 * @param EventManager $eventManager
	 */
	public static function setEventManager($eventManager)
	{
		self::$eventManager = $eventManager;
	}

	/**
	 * @return ?Route
	 */
	public static function getRoute(): ?Route
	{
		return self::$route;
	}

	/**
	 * @param Route $route
	 */
	public static function setRoute(Route $route): void
	{
		self::$route = $route;
	}

	/**
	 * @return Router
	 */
	public static function getRouter(): Router
	{
		return self::$router;
	}

	/**
	 * @param Router $router
	 */
	public static function setRouter(Router $router): void
	{
		self::$router = $router;
	}

	public static function getCache($poolName = 'default'): Pool
	{
		if (!isset(static::$cachePools[$poolName]))
		{
			//$config = static::$configuration->get('cache.'.$poolName);
			$config = static::getConfiguration()->get('cache.'.$poolName);

			if (empty($config))
			{
				throw new \Exception;
			}

			$adapterClass = $config['adapter'];
			//$pool = new Pool(new $adapterClass(PROJECT_ROOT.'/cache'));
			$pool = new Pool(new $adapterClass($config['options']));

			static::$cachePools[$poolName] = $pool;
			static::$kernel->registerTerminateHandler(function () use ($pool) {
				$pool->commit();
			});
		}

		return static::$cachePools[$poolName];
	}
}
