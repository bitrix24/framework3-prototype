<?php

namespace Bitrix\Main\Lib;

trait PackageLeadClassTrait
{
	public function getPath()
	{
		$refClass = new \ReflectionClass($this);
		return dirname($refClass->getFileName());
	}

	public function getProjectPath()
	{
		return str_replace(PROJECT_ROOT, '', $this->getPath());
	}
}