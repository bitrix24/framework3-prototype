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
use Twig\Node\NodeOutputInterface;

/**
 * @package    bitrix
 * @subpackage main
 */
abstract class BufferedNode extends Node implements NodeOutputInterface
{
	public const OUTPUT_MODE_ECHO = 1;
	public const OUTPUT_MODE_BUFFERED = 2;
	public const OUTPUT_MODE_CONCAT = 3;
	public const OUTPUT_MODE_RAW = 4;

	protected $outputMode = self::OUTPUT_MODE_BUFFERED;

	protected $concatVarName;

	public function __construct(Node $node)
	{
		parent::__construct(['source_node' => $node]);
	}

	/**
	 * @param int $outputMode
	 */
	public function setOutputMode($outputMode): void
	{
		$this->outputMode = $outputMode;
	}

	/**
	 * @param string $concatVarName
	 */
	public function setConcatVarName($concatVarName): void
	{
		$this->concatVarName = $concatVarName;
	}

	public function compile(Compiler $compiler)
	{
		$compiler->addDebugInfo($this->getNode('source_node'));

		switch ($this->outputMode)
		{
			case self::OUTPUT_MODE_ECHO:
				$this->compileEcho($compiler);
				break;

			case self::OUTPUT_MODE_BUFFERED:
				$this->compileBuffered($compiler);
				break;

			case self::OUTPUT_MODE_CONCAT:
				$this->compileConcat($compiler);
				break;

			case self::OUTPUT_MODE_RAW:
				$this->compileRaw($compiler);
				break;
		}
	}

	public function compileEcho(Compiler $compiler): void
	{
		$compiler
			->subcompile($this->getNode('source_node'))
		;
	}

	public function compileBuffered(Compiler $compiler): void
	{
		$compiler->write('$this->extensions["'.StackExtension::class.'"]->output(');
		$this->compileContent($compiler);
		$compiler->raw(");\n");
	}

	public function compileConcat(Compiler $compiler): void
	{
		$compiler->write('$'.$this->concatVarName.' .= ');
		$this->compileContent($compiler);
		$compiler->raw(";\n");
	}

	public function compileRaw(Compiler $compiler): void
	{
		$compiler->write('');
		$this->compileContent($compiler);
		$compiler->raw(";\n\n");
	}

	public function compileContent(Compiler $compiler)
	{
		$compiler
			->subcompile($this->getNode('source_node'))
		;
	}
}
