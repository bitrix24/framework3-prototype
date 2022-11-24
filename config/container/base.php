<?php

use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Whoops\Run;

return [
	Run::class => function (ContainerInterface $container) {
		$whoops = new Run;
		$whoops->allowQuit(false);
		$whoops->writeToOutput(false);
		$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);

		return $whoops;
	},

	LoggerInterface::class => function (ContainerInterface $container) {
		$logger = new Logger('name');
		//$logger->pushHandler(new StreamHandler('/www/app.log', Logger::DEBUG));
		$logger->pushHandler(new FirePHPHandler());

		return $logger;
	}
];
