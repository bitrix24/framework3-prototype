<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Stack;


class StackPointer
{
    protected $name;

	/**
	 * StackPointer constructor.
	 *
	 * @param $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}


	public function __toString()
    {
        return $this->name;
    }
} 