<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Bitrix\Node;

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Site;
use Twig\Compiler;
use Twig\Node\Expression\ConstantExpression;

/**
 * Generates content for "doGetParent" template cache method
 * @package    bitrix
 * @subpackage main
 */
class LayoutExpression extends ConstantExpression
{
	/**
	 * @see Context::getSite()
	 * @see Site::loadLayout()
	 *
	 * @param Compiler $compiler
	 * @return void
	 */
	public function compile(Compiler $compiler): void
	{
		$compiler
			->raw('\\'. Context::class.'::getSite()->loadLayout(')
			->repr($this->getAttribute('value'))
			->raw(')')
		;
	}

}
