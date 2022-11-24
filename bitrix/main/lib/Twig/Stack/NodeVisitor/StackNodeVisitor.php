<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Stack\NodeVisitor;

use Bitrix\Main\Lib\Twig\Stack\Extension\StackExtension;
use Bitrix\Main\Lib\Twig\Stack\Node\BufferedConcatNode;
use Bitrix\Main\Lib\Twig\Stack\Node\BufferedNode;
use Bitrix\Main\Lib\Twig\Stack\Node\BufferedPrintNode;
use Bitrix\Main\Lib\Twig\Stack\Node\DeferredNode;
use Bitrix\Main\Lib\Twig\Stack\Node\PushNode;
use Twig\Environment;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\IfNode;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;


class StackNodeVisitor implements NodeVisitorInterface
{
	/** @var ModuleNode */
	protected $moduleNode;

    /**
     * Called before child nodes are visited.
     *
     * @param Node $node The node to visit
     * @param Environment $env The Twig environment instance
     * @return Node The modified node
     */
    public function enterNode(Node $node, Environment $env): Node
    {
		if ($node instanceof ModuleNode)
		{
			$this->moduleNode = $node;
		}
		elseif ($node instanceof PushNode)
		{
			// wrap content with concat
			$body = $node->getNode('body');

			$node->setNode('body',
				new BufferedConcatNode($body, 'out_'.uniqid())
			);
		}

		return $node;
    }

    /**
     * Called after child nodes are visited.
     *
     * @param Node $node The node to visit
     * @param Environment $env The Twig environment instance
     * @return Node|false The modified node or false if the node must be removed
     */
    public function leaveNode(Node $node, Environment $env): ?Node
    {
        if ($node instanceof BufferedPrintNode)
        {
			$expr = $node->getNode('source_node')->getNode('expr');

			if ($expr instanceof FunctionExpression
				&& $expr->getAttribute('name') === StackExtension::STACK_SHOW_FUNCTION_NAME)
			{
				// no output for showing stack
				$node->setOutputMode(BufferedNode::OUTPUT_MODE_RAW);
			}
			elseif ($expr instanceof FilterExpression && $this->searchForStackFunctionAndReplace($expr))
			{
				// filter on stack should be deferred
				$node = new DeferredNode($node, $this->moduleNode);
			}
        }
		elseif ($node instanceof IfNode  && $this->searchForStackFunctionAndReplace($node->getNode('tests')))
		{
			$node = new DeferredNode($node, $this->moduleNode);
		}

        return $node;
    }

    /**
     * Returns the priority for this visitor.
     * Priority should be between -10 and 10 (0 is the default).
     *
     * @return integer The priority level
     */
    public function getPriority()
    {
        return 5;
    }

	protected function searchForStackFunctionAndReplace(Node $node)
	{
		$found = false;

		foreach ($node as $subNode)
		{
			if ($subNode instanceof FunctionExpression
				&& $subNode->getAttribute('name') === StackExtension::STACK_SHOW_FUNCTION_NAME)
			{
				$subNode->setAttribute('name', StackExtension::STACK_GET_FUNCTION_NAME);
				$found = true;
			}

			if (count($subNode))
			{
				$search = $this->searchForStackFunctionAndReplace($subNode);

				if ($search)
				{
					$found = true;
				}
			}
		}

		return $found;
	}
}