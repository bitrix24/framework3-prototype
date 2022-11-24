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
use Twig\Node\ModuleNode;
use Twig\Node\Node;


class BufferedBodyNode extends Node
{
	/** @var ModuleNode */
	protected $moduleNode;

	/**
	 * Construct the stack body with the original body
	 *
	 * @param Node       $body
	 * @param ModuleNode $moduleNode
	 */
    public function __construct(Node $body, ModuleNode $moduleNode)
    {
    	$this->moduleNode = $moduleNode;

        parent::__construct(array('body' => $body));
    }

	public function compile(Compiler $compiler): void
	{
		$compiler->write("\n", '$context = $this->extensions["'.StackExtension::class."\"]->mergeGlobals(\$context);\n\n");
		$compiler->write("\n", '$this->extensions["'.StackExtension::class."\"]->startRender();\n\n");
		parent::compile($compiler);
		$compiler->write("\n", '$this->extensions["'.StackExtension::class."\"]->endRender();\n");
	}
} 