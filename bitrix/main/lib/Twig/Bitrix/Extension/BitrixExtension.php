<?php

namespace Bitrix\Main\Lib\Twig\Bitrix\Extension;

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Controllers\ControllerFactory;
use Bitrix\Main\Lib\Controllers\Extension;
use Bitrix\Main\Lib\Exceptions\ArgumentException;
use Bitrix\Main\Lib\Twig\Bitrix\NodeVisitor\ExtendsNodeVisitor;
use Bitrix\Main\Lib\Twig\Bitrix\TokenParser\LayoutParser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

class BitrixExtension extends AbstractExtension
{
	public function getFunctions()
	{
		return [
			new TwigFunction(
				'component',
				[$this, 'includeComponent'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'resource',
				[$this, 'getResourcePath']
			),
			new TwigFunction(
				'asset',
				[$this, 'includeAsset'],
				['is_safe' => ['html']]
			),
			new TwigFunction(
				'extension',
				[$this, 'includeExtension']
			),
		];
	}

	/**
	 * Returns the token parser instances to add to the existing list.
	 *
	 * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
	 */
	public function getTokenParsers()
	{
		return [
			new LayoutParser,
		];
	}

	/**
	 * Returns the node visitor instances to add to the existing list.
	 */
	public function getNodeVisitors()
	{
		return [
			new ExtendsNodeVisitor
		];
	}

	public function includeComponent(array $controllerAction, array $context = [], $configurationAlias = null)
	{
		[$controllerClass, $actionName] = $controllerAction;
		$container = Context::getContainer();

		$controllerFactory = $container->get(ControllerFactory::class);
		$controller = $controllerFactory->create($controllerClass, configurationAlias: $configurationAlias);

		$route = Context::getRoute();

		// parameters for this call
		foreach ($context as $parameterName => $parameterValue)
		{
			if (!property_exists($controller, $parameterName))
			{
				throw new ArgumentException(sprintf(
					'Controller `%s` has no property `%s`', get_class($controller), $parameterName
				));
			}

			$controller->{$parameterName} = $parameterValue;
		}

		// call action
		return $container->call([$controller, $actionName], $route->getParametersValues()->getValues());
	}

	public function getResourcePath($path)
	{
		Context::getAssetManager()->getResourcePath($path);
	}

	public function includeAsset($asset)
	{
		Context::getAssetManager()->addAsset($asset);
	}

	/**
	 * @param Extension|string $extensionClass
	 * @return mixed
	 */
	public function includeExtension($extensionClass)
	{
		/** @var Extension $extension */
		$extension = new $extensionClass;

		foreach ($extension->getAssets() as $asset)
		{
			$this->includeAsset($asset);
		}
	}
}
