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
use Twig\Node\NodeOutputInterface;

/**
 * Container for BufferedNode that puts content into deferred callback
 *
 * @package    bitrix
 * @subpackage main
 */
class DeferredNode extends Node implements NodeOutputInterface
{
	/** @var ModuleNode */
	protected $moduleNode;

	public function __construct(Node $node, ModuleNode $moduleNode)
	{
		$this->moduleNode = $moduleNode;
		parent::__construct(['source_node' => $node]);
	}

	public function compile(Compiler $compiler)
	{
		$uniqName = "stack_".uniqid();

		$compiler
			->write('$this->extensions["'.StackExtension::class.'"]->output(')
			->raw('[$this, "'.$uniqName.'"]')
			->raw(");\n")
		;

		$this->moduleNode->getNode('class_end')->setNode(
			$uniqName, new DeferredBodyNode($uniqName, $this->getNode('source_node'))
		);
	}
}
