<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Stack\Node;

use Twig\Compiler;
use Twig\Node\PrintNode;

/**
 * @package    bitrix
 * @subpackage main
 */
class BufferedPrintNode extends BufferedNode
{
	public function __construct(PrintNode $node)
	{
		parent::__construct($node);
	}

	public function compileContent(Compiler $compiler)
	{
		$compiler
			->subcompile($this->getNode('source_node')->getNode('expr'))
		;
	}
}
