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
 * Container for BufferedNode with concat mode
 *
 * @package    bitrix
 * @subpackage main
 */
class BufferedConcatNode extends Node
{
	protected $concatVarName = 'outputBuffer';

	public function __construct(Node $body, $concatVarName = null)
	{
		parent::__construct(['body' => $body]);

		if ($concatVarName !== null)
		{
			$this->concatVarName = $concatVarName;
		}

		$this->setConcatOutputMode($this);
	}

	/**
	 * @return string
	 */
	public function getConcatVarName()
	{
		return $this->concatVarName;
	}

	public function compile(Compiler $compiler)
	{
		$compiler
			->write("\${$this->concatVarName} = \"\";\n")
		;

		$compiler->subcompile($this->getNode('body'));
	}

	public function setConcatOutputMode(Node $node)
	{
		foreach ($node as $subNode)
		{
			if ($subNode instanceof BufferedNode)
			{
				$subNode->setOutputMode(BufferedNode::OUTPUT_MODE_CONCAT);
				$subNode->setConcatVarName($this->concatVarName);
			}
			elseif (count($subNode))
			{
				$this->setConcatOutputMode($subNode);
			}
		}
	}
}
