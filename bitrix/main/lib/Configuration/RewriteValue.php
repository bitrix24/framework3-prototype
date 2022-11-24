<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Configuration;

/**
 * Used for replacing
 *
 * @package    bitrix
 * @subpackage main
 */
class RewriteValue
{
	/** @var array */
	protected $values;

	public function __construct(array $values)
	{
		$this->values = $values;
	}

	/**
	 * @return array
	 */
	public function getValues(): array
	{
		return $this->values;
	}
}
