<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing\Controllers;

use Bitrix\Main\Lib\Application;
use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Engine\AutoWire\Parameter;
use DI\Container;
use DI\ContainerBuilder;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @package    bitrix
 * @subpackage main
 */
class TwigPageController extends Controller
{
	/** @var string */
	protected $pagePath;

	/**
	 * @param string $path
	 */
	public function __construct($path)
	{
		$this->pagePath = $path;
	}

	public function execute()
	{
		$site = Context::getSite();
		$path = $site->getPagePath($this->pagePath);
		$response = new \Bitrix\Main\Lib\Response($path, []);

		return new Response(200, [], $response->render());
	}
}
