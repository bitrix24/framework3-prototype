<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib;

use Bitrix\Main\Lib\Configuration\Configuration;
use Bitrix\Main\Lib\Exceptions\ErrorResponseException;
use Bitrix\Main\Lib\Routing\RouteMatchMiddleware;
use Bitrix\Main\Lib\Routing\Router;
use Bitrix\Main\Lib\Routing\RoutingConfigurator;
use Bitrix\Main\Lib\Twig\Stack\Extension\StackExtension;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Twig\Error\RuntimeError;
use Whoops\Run;

/**
 * Class description
 * @package    bitrix
 * @subpackage main
 */
class Kernel
{
	protected ContainerInterface $container;

	protected $terminateHandlers = [];

	public function __construct()
	{
		$this->container = Context::getContainer();
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		// router init
		$routes = $this->container->make(RoutingConfigurator::class);
		$router = $this->container->make(Router::class);
		$routes->setRouter($router);

		Context::setRouter($router);

		// router configuration
		$files[] = PROJECT_ROOT.'/config/routes/web.php'; // TODO all files from dir
		foreach ($files as $file)
		{
			$callback = include $file;
			$callback($routes);
		}

		// router compile
		$router->releaseRoutes();

		// cache for route compiled data
		//CompileCache::handle($files, $router);

		$requestHandler = new QueueRequestHandler;

		// add default middleware from config
		$middlewares = $this->container->get(Configuration::class)->get('middleware');

		if (!empty($middlewares) && is_array($middlewares))
		{
			foreach ($middlewares as $middleware)
			{
				$middleware = is_object($middleware)
					? $middleware
					: $this->container->make($middleware);

				$requestHandler->addMiddleware($middleware);
			}
		}

		// add routing middleware - matching, specific by route, calling controller
		$routeMatchMiddleware = new RouteMatchMiddleware($router);
		$requestHandler->addMiddleware($routeMatchMiddleware);

		// execute middleware and route matching
		try
		{
			$response = $requestHandler->handle($request);
		}
		catch (\Exception $e)
		{
			return $this->handleThrowable($e);
		}

		return $response;
	}

	protected function handleThrowable(\Throwable $e): ResponseInterface
	{
		if ($e instanceof RuntimeError && $e->getPrevious() instanceof ErrorResponseException)
		{
			return $this->handleThrowable($e->getPrevious());
		}

		if ($e instanceof ErrorResponseException)
		{
			// reset output buffer
			$env = Response::getTwigEnvironment();

			/** @var StackExtension $stackExt */
			$stackExt = $env->getExtension(StackExtension::class);
			$stackExt->reset();

			try
			{
				// generate response, get custom controller from exception
				$content = $e->getController()->execute();

				if ($content instanceof \GuzzleHttp\Psr7\Response)
				{
					return $content;
				}

				return new \GuzzleHttp\Psr7\Response(status: $e->getCode(), body: $content);
			}
			catch (\Exception $e)
			{
				return new \GuzzleHttp\Psr7\Response(status: 503, body: 'logged error');
			}
		}

		return $this->renderThrowable($e);
	}

	public function renderThrowable(\Throwable $e): ResponseInterface
	{
		// default: show trace for dev, log and show 503 default for prod
		$env = $this->container->get(Configuration::class)->get('app.environment');

		if ($env === 'dev')
		{
			if ($this->container->has(Run::class))
			{
				$whoops = $this->container->get(Run::class);
				$html = $whoops->handleException($e);

				return new \GuzzleHttp\Psr7\Response(status: 503, body: $html);
			}
			else
			{
				// php output
				throw $e;
			}
		}
		else
		{
			$logger = $this->container->get(LoggerInterface::class);
			$logger->error($e);

			// show 503 error
			return $this->handleThrowable(new ErrorResponseException($e->getMessage(), 503));
		}
	}

	public function registerTerminateHandler($callable)
	{
		$this->terminateHandlers[] = $callable;
	}

	public function terminate()
	{
		foreach ($this->terminateHandlers as $handler)
		{
			call_user_func($handler);
		}
	}
}
