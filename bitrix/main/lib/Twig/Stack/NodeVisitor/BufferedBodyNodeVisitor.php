<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Stack\NodeVisitor;

use Bitrix\Main\Lib\Twig\Stack\Node\BufferedBodyNode;
use Twig\Environment;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;


class BufferedBodyNodeVisitor implements NodeVisitorInterface
{
    /**
     * Called before child nodes are visited.
     *
     * @param Node $node The node to visit
     * @param Environment $env The Twig environment instance
     * @return Node The modified node
     */
    public function enterNode(Node $node, Environment $env): Node
    {
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
        if ($node instanceof ModuleNode)
        {
            $this->handleModuleNode($node);
        }

        return $node;
    }

	private function handleModuleNode(ModuleNode $node)
	{
		if ($node->hasNode('body') && !$node->hasNode('parent')) {
			$body = $node->getNode('body');
			$node->setNode('body', new BufferedBodyNode($body, $node));
		}
	}

    /**
     * Returns the priority for this visitor.
     * Priority should be between -10 and 10 (0 is the default).
     *
     * @return integer The priority level
     */
    public function getPriority()
    {
        return 0;
    }
}