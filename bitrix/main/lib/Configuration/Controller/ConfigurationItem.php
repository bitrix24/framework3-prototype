<?php

namespace Bitrix\Main\Lib\Configuration\Controller;

class ConfigurationItem
{
	protected array $routeNames;

	protected array $componentAliases;

	protected \Closure $callback;

	/**
	 * @return array
	 */
	public function getRouteNames(): array
	{
		return $this->routeNames;
	}

	/**
	 * @param array $routeNames
	 */
	public function setRouteNames(array $routeNames): void
	{
		$this->routeNames = $routeNames;
	}

	/**
	 * @return array
	 */
	public function getComponentAliases(): array
	{
		return $this->componentAliases;
	}

	/**
	 * @param array $componentAliases
	 */
	public function setComponentAliases(array $componentAliases): void
	{
		$this->componentAliases = $componentAliases;
	}

	/**
	 * @return \Closure
	 */
	public function getCallback(): \Closure
	{
		return $this->callback;
	}

	/**
	 * @param \Closure $callback
	 */
	public function setCallback(\Closure $callback): void
	{
		$this->callback = $callback;
	}
}