<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Stack\Node;

use Twig\Compiler;
use Twig\Node\TextNode;

/**
 * @package    bitrix
 * @subpackage main
 */
class BufferedTextNode extends BufferedNode
{
	public function __construct(TextNode $node)
	{
		parent::__construct($node);
	}

	public function compileContent(Compiler $compiler)
	{
		$compiler
			->string($this->getNode('source_node')->getAttribute('data'))
		;
	}
}
