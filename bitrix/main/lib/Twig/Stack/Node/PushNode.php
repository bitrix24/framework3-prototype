<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Stack\Node;

use Bitrix\Main\Lib\Twig\Stack\Extension\StackExtension;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * @package    bitrix
 * @subpackage main
 */
class PushNode extends Node
{
	public function __construct(string $name, Node $body, int $lineno, string $tag = null)
	{
		parent::__construct(['body' => $body], ['name' => $name], $lineno, $tag);
	}

	public function compile(Compiler $compiler): void
	{
		/** @var BufferedConcatNode $body */
		$body = $this->getNode('body');

		$compiler
			->addDebugInfo($this)
			->subcompile($body)
			->write(sprintf(
				"\$this->extensions['".StackExtension::class."']->pushStack('%s', ",// '%s');\n\n",
				$this->getAttribute('name')
			))
			->raw("\${$body->getConcatVarName()});\n")
		;
	}
}
