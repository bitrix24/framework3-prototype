<?php

namespace Bitrix\Main\Lib\Routing\Controllers;

use Bitrix\Main\Lib\Context;
use GuzzleHttp\Psr7\Response;

class FallbackController
{
	protected \Closure $closure;

	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure)
	{
		// TODO not only closure
		$this->closure = $closure;
	}

	public function __invoke()
	{
		$container = Context::getContainer();

		$result = $container->call($this->closure);

		if (!($result instanceof Response))
		{
			$result = new Response(status: 200, body: $result);
		}

		return $result;
	}
}