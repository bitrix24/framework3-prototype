<?php

namespace Bitrix\Main\Components\ErrorPages;

use Bitrix\Main\Lib\Controllers\Controller;

class ErrorPagesController extends Controller
{
	public static array $codes = [403, 404, 503];

	public function show403Error()
	{
		return $this->render('default/403');
	}

	public function show404Error()
	{
		return $this->render('default/404');
	}

	public function show503Error()
	{
		return $this->render('default/503');
	}
}