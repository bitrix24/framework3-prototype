<?php

namespace Bitrix\Main\Lib;

abstract class Layout
{
	use PackageLeadClassTrait;

	public function getFileName()
	{
		return 'layout.twig';
	}

	public function getFileProjectPath()
	{
		return $this->getProjectPath() . '/' . $this->getFileName();
	}

	public function getData()
	{
		return [];
	}
}