<?php

namespace Bitrix\Main\Lib\Controllers;

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Exceptions\ErrorResponseException;
use Bitrix\Main\Lib\PackageLeadClassTrait;
use Bitrix\Main\Lib\Response;
use Bitrix\Main\Lib\Templates\Template;
use Bitrix\Main\Lib\Twig\Bitrix\Extension\BitrixExtension;

class Controller
{
	use PackageLeadClassTrait;

	protected ?array $templateRouterContext = null;

	/**
	 * @return array|null
	 */
	public function getTemplateRouterContext(): ?array
	{
		return $this->templateRouterContext;
	}

	/**
	 * @param mixed $templateRouterContext
	 */
	public function setTemplateRouterContext(array $templateRouterContext): void
	{
		$this->templateRouterContext = $templateRouterContext;
	}

	public function render($viewName, $context = []): string
	{
		$template = $this->getPath() . '/templates/'.$viewName;
		$templateDir = dirname($template);

		// include default assets
		$templateStyle = $templateDir.'/resources/css/style.css';
		if (file_exists($templateStyle))
		{
			$styleInProject = str_replace(PROJECT_ROOT, '', $templateStyle);

			$env = Response::getTwigEnvironment();
			/** @var BitrixExtension $bitrixExt */
			$bitrixExt = $env->getExtension(BitrixExtension::class);
			$bitrixExt->includeAsset($styleInProject);
		}

		// add template in context
		$tpl = new Template($this->templateRouterContext);
		$context['this'] = $tpl;

		return (new Response($template, $context))->render();
	}

	protected function forwardNotFound($customMessageId = null)
	{
		$router = Context::getRouter();
		$fallback = $router->getFallbackNotFound();

		// TODO use $fallback from router?
		throw new ErrorResponseException($customMessageId, 404/*, '404.twig'*/);
	}
}