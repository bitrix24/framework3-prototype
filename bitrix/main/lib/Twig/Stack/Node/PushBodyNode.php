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
use Twig\Node\PrintNode;
use Twig\Node\TextNode;

/**
 * @package    bitrix
 * @subpackage main
 */
class PushBodyNode extends Node
{
	public function __construct(Node $body, int $lineno, string $tag = null)
	{
		parent::__construct(['body' => $body], [], $lineno, $tag);
	}

	public function compile(Compiler $compiler): void
	{
		$this->compileNode($this, $compiler);

		$compiler->raw('""');
	}

	public function compileNode(Node $node, Compiler $compiler)
	{
		foreach ($node->nodes as $subNode)
		{
			/** @var $subNode Node */
			if ($subNode instanceof PrintNode)
			{
				$compiler
					->addDebugInfo($subNode)
					->subcompile($subNode->getNode('expr'))
					->raw(".\n")
				;
			}
			elseif ($subNode instanceof TextNode)
			{
				$compiler
					->addDebugInfo($subNode)
					->string($subNode->getAttribute('data'))
					->raw(".\n")
				;
			}
			else
			{
				if (!empty($subNode->nodes))
				{
					$this->compileNode($subNode, $compiler);
				}
				else
				{
					$subNode->compile($compiler);
				}
			}
		}
	}
}
