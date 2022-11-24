<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing\Controllers;

use Bitrix\Main\Lib\Context;
use CBXVirtualIo;

/**
 * @package    bitrix
 * @subpackage main
 */
class PublicPageController extends Controller
{
	protected $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function execute()
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_io.php");
		$io = CBXVirtualIo::GetInstance();

		$_SERVER["REAL_FILE_PATH"] = $this->getPath();
		Context::getCurrent()->getServer()->set('REAL_FILE_PATH', $this->getPath());

		include_once($io->GetPhysicalName($_SERVER['DOCUMENT_ROOT'].$this->getPath()));
		die;
	}

	/**
	 * @return mixed
	 */
	public function getPath()
	{
		return $this->path;
	}
}
