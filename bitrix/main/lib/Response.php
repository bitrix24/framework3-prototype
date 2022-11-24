<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib;

use Bitrix\Main\Lib\Twig\Bitrix\Extension\BitrixExtension;
use Bitrix\Main\Lib\Twig\Stack\Extension\StackExtension;
use Twig\Cache\FilesystemCache;

/**
 * Class description
 * @package    bitrix
 * @subpackage main
 */
class Response // TODO ControllerResponse
{
	protected $template;

	protected $context;

	/** @var \Twig\Environment */
	protected static $twig;

	/**
	 * Response constructor.
	 */
	public function __construct($template, $context)
	{
		$this->template = str_replace(PROJECT_ROOT.'/', '', $template);
		$this->template = str_replace('.twig', '', $this->template); // TODO replace to if_ends_with
		$this->context = $context;
	}

	public function render()
	{
		$twig = static::getTwigEnvironment();

		return $twig->render($this->template.'.twig', $this->context);
	}

	public function __toString()
	{
		return $this->render();
	}

	/**
	 * TODO: Container?
	 * @return \Twig\Environment
	 */
	public static function getTwigEnvironment()
	{
		if (static::$twig === null)
		{
			$loader = new \Twig\Loader\FilesystemLoader(PROJECT_ROOT);
			$twig = new \Twig\Environment($loader, [
				'debug' => true,
				'cache' => new FilesystemCache(PROJECT_ROOT . '/cache/twig'),
			]);

			$twig->addGlobal('route', Context::getRoute());

			$twig->addExtension(new StackExtension());
			$twig->addExtension(new BitrixExtension());

			static::$twig = $twig;
		}

		return static::$twig;
	}

}
