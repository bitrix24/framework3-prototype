<?php

namespace Bitrix\Main\Components\MaintenanceMode;

use GuzzleHttp\Psr7\Response;

class MaintenanceModeController extends \Bitrix\Main\Lib\Controllers\Controller
{
	public function showMaintenanceMessage()
	{
		$responseText = $this->render('default/onMaintenance');

		return new Response(status: 503, body: $responseText); // TODO response factory
	}
}