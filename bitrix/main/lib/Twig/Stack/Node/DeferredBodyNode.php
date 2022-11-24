<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Stack\Node;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * @package    bitrix
 * @subpackage main
 */
class DeferredBodyNode extends Node
{
	public function __construct($name, Node $body)
	{
		$body = new BufferedConcatNode($body);
		parent::__construct(['body' => $body], ['name' => $name]);
	}

	public function compile(Compiler $compiler): void
	{
		/** @var BufferedConcatNode $body */
		$body = $this->getNode('body');

		$compiler
			->write(sprintf("public function %s()\n", $this->getAttribute('name')), "{\n")
			->indent()
			->subcompile($body)
			->write('return $'.$body->getConcatVarName().";\n")
			->outdent()
			->write("}\n\n")
		;
	}
}
