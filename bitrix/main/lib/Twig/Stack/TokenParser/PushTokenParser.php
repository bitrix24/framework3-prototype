<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Stack\TokenParser;

use Bitrix\Main\Lib\Twig\Stack\Node\PushNode;
use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * @package    bitrix
 * @subpackage main
 */
class PushTokenParser extends AbstractTokenParser
{
	public function parse(Token $token): Node
	{
		$lineno = $token->getLine();
		$stream = $this->parser->getStream();
		$name = $stream->expect(Token::NAME_TYPE)->getValue();

		$this->parser->pushLocalScope();

		if ($stream->nextIf(Token::BLOCK_END_TYPE)) {
			$body = $this->parser->subparse([$this, 'decideBlockEnd'], true);
			if ($token = $stream->nextIf(/* Token::NAME_TYPE */ 5)) {
				$value = $token->getValue();

				if ($value != $name) {
					throw new SyntaxError(sprintf('Expected endpush for block "%s" (but "%s" given).', $name, $value), $stream->getCurrent()->getLine(), $stream->getSourceContext());
				}
			}
		} else {
			$body = $this->parser->getExpressionParser()->parseExpression();
		}
		$stream->expect(Token::BLOCK_END_TYPE);

		$this->parser->popLocalScope();

		return new PushNode($name, $body, $lineno, $this->getTag());
	}

	public function decideBlockEnd(Token $token): bool
	{
		return $token->test('endpush');
	}

	public function getTag(): string
	{
		return 'push';
	}
}
