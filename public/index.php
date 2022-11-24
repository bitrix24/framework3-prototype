<?php

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Kernel;
use GuzzleHttp\Psr7\ServerRequest;

/** Composer autoload */
require __DIR__.'/../vendor/autoload.php';

/** Run Bitrix Core */
require __DIR__.'/../config/bootstrap.php';

// route matching
$kernel = new Kernel;
$request = ServerRequest::fromGlobals();
Context::setKernel($kernel);

$response = $kernel->handle($request);

echo $response->getBody();

$kernel->terminate();