<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Bitrix\NodeVisitor;

use Bitrix\Main\Lib\Twig\Bitrix\Node\LayoutExpression;
use Twig\Environment;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;


/**
 * Adds absolute project path to "extends" tag
 * @package Bitrix\Main\Lib\Twig\Bitrix\NodeVisitor
 */
class ExtendsNodeVisitor implements NodeVisitorInterface
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
    	// if template with parent (extends)
		if ($node instanceof ModuleNode && $node->hasNode('parent'))
		{
			$parentNode = $node->getNode('parent');

			// layout has its own login
			if (!($parentNode instanceof LayoutExpression))
			{
				$templateName = $parentNode->getAttribute('value');

				if (strpos($templateName, '/') !== 0)
				{
					// relative path, add source template's path
					$sourceTemplate = $node->getSourceContext()->getName();
					$sourceTemplatePath = dirname($sourceTemplate);

					$parentNode->setAttribute(
						'value',
						$sourceTemplatePath.'/'.$templateName
					);
				}
			}
		}

		// includes
		if (
			$node instanceof FunctionExpression
			&& $node->getAttribute('name') === 'include'
		)
		{
			// get first argument of function - path of template to include
			$fileToIncludeNode = $node->getNode('arguments')->getNode('0');

			if ($fileToIncludeNode instanceof ConstantExpression)
			{
				$templateName = $fileToIncludeNode->getAttribute('value');

				if (strpos($templateName, '/') !== 0)
				{
					// relative path, add source template's path
					$sourceTemplate = $node->getSourceContext()->getName();
					$sourceTemplatePath = dirname($sourceTemplate);

					$fileToIncludeNode->setAttribute(
						'value',
						$sourceTemplatePath.'/'.$templateName
					);
				}
			}
		}

		// resources
		if (
			$node instanceof FunctionExpression
			&& (
				$node->getAttribute('name') === 'resource'
				|| $node->getAttribute('name') === 'asset'
			)
		)
		{
			// get first argument of function - path of template to include
			$fileToIncludeNode = $node->getNode('arguments')->getNode('0');

			if ($fileToIncludeNode instanceof ConstantExpression)
			{
				$templateName = $fileToIncludeNode->getAttribute('value');

				if (strpos($templateName, '/') !== 0)
				{
					// relative path, add source template's path
					$sourceTemplate = $node->getSourceContext()->getName();
					$sourceTemplatePath = dirname($sourceTemplate);

					$fileToIncludeNode->setAttribute(
						'value',
						$sourceTemplatePath.'/'.$templateName
					);
				}
			}
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
}