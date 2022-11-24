<?php

namespace Sites\s3;

use Bitrix\Main\Layouts\LightV3\LightV3Layout;
use Bitrix\Main\Sites\Supershop\SupershopSite;

class Site extends SupershopSite
{
	protected $layout = LightV3Layout::class;
}