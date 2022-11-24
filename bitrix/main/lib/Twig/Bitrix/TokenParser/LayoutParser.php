<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Bitrix\TokenParser;

use Bitrix\Main\Lib\Twig\Bitrix\Node\LayoutExpression;
use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Parses 'layout' tag
 * @package    bitrix
 * @subpackage main
 */
class LayoutParser extends AbstractTokenParser
{
	public function parse(Token $token): Node
	{
		$stream = $this->parser->getStream();

		if ($this->parser->peekBlockStack()) {
			throw new SyntaxError('Cannot use "layout" in a block.', $token->getLine(), $stream->getSourceContext());
		} elseif (!$this->parser->isMainScope()) {
			throw new SyntaxError('Cannot use "layout" in a macro.', $token->getLine(), $stream->getSourceContext());
		}

		if (null !== $this->parser->getParent()) {
			throw new SyntaxError('Multiple extends tags are forbidden.', $token->getLine(), $stream->getSourceContext());
		}

		if ($stream->test(/* Token::BLOCK_END_TYPE */ 3))
		{
			$value = null;
		} else {
			$parent = $this->parser->getExpressionParser()->parseExpression();
			$value = $parent->getAttribute('value');
		}

		$parent = new LayoutExpression($value, $token->getLine());

		$this->parser->setParent($parent);



		$stream->expect(/* Token::BLOCK_END_TYPE */ 3);

		return new Node();
	}

	public function getTag(): string
	{
		return 'layout';
	}
}
