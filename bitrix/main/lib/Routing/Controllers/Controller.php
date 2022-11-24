<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing\Controllers;

use Bitrix\Main\Lib\Routing\Route;
use GuzzleHttp\Psr7\Response;

/**
 * @package    bitrix
 * @subpackage main
 */
abstract class Controller
{
	protected ?Route $route = null;

	/**
	 * @return Route
	 */
	public function getRoute(): Route
	{
		return $this->route;
	}

	/**
	 * @param Route $route
	 */
	public function setRoute(Route $route): void
	{
		$this->route = $route;
	}

	abstract public function execute();

	public function __invoke()
	{
		$result = $this->execute();

		if (!($result instanceof Response))
		{
			$result = new Response(200, [], $result);
		}

		return $result;
	}
}
