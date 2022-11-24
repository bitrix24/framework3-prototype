<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Stack\Extension;

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Event;
use Bitrix\Main\Lib\Response;
use Bitrix\Main\Lib\Twig\Stack\NodeVisitor\BufferedBodyNodeVisitor;
use Bitrix\Main\Lib\Twig\Stack\NodeVisitor\BufferedOutputNodeVisitor;
use Bitrix\Main\Lib\Twig\Stack\NodeVisitor\StackNodeVisitor;
use Bitrix\Main\Lib\Twig\Stack\Stack;
use Bitrix\Main\Lib\Twig\Stack\StackPointer;
use Bitrix\Main\Lib\Twig\Stack\TokenParser\PushTokenParser;
use Twig\Extension\AbstractExtension;
use Twig\Node\Node;
use Twig\TwigFunction;


class StackExtension extends AbstractExtension
{
	const STACK_SHOW_FUNCTION_NAME = 'slot';

	const STACK_GET_FUNCTION_NAME = 'get_slot';


	/**
     * @var array|Stack[]
     */
    protected $deferredStack = [];

    protected $outputStack = [];

    protected $renderRequestLevel = -1;

    /**
     * @var array Analogue of twig env globals that allows add parameters after twig init
     */
    protected $globals = [];

    /**
     * Push the given content to the stack identified by its name
     * If the stack does not exist, create it
     *
     * @param string $stackName
     * @param string $content
     */
    public function pushStack($stackName, $content, $prepend = false)
    {
        if (!array_key_exists($stackName, $this->deferredStack))
        {
            $this->deferredStack[$stackName] = new Stack;
        }

        $this->deferredStack[$stackName]->push($content, $prepend);
    }

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return [
        	new PushTokenParser
		];
    }

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return Node[] An array of Twig_NodeVisitorInterface instances
     */
    public function getNodeVisitors()
    {
        return [
            new BufferedBodyNodeVisitor,
			new BufferedOutputNodeVisitor,
			new StackNodeVisitor,
		];
    }

	public function getFunctions()
	{
		return [
			new TwigFunction(static::STACK_SHOW_FUNCTION_NAME, [$this, 'showStackContent'], ['is_safe' => ['all']]),
			new TwigFunction(static::STACK_GET_FUNCTION_NAME, [$this, 'getStackContent'], ['is_safe' => ['all']])
		];
	}

	public function showStackContent($name)
	{
		$this->outputStack[] = new StackPointer($name);
	}

	public function getStackContent($name)
	{
		return isset($this->deferredStack[$name]) ? (string) $this->deferredStack[$name] : '';
	}

	public function output($str)
	{
		$this->outputStack[] = $str;
	}

    /**
     * @param string $name
     * @param mixed $value
     */
    public function addGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

    /**
     * @param array $context
     * @return array
     */
    public function mergeGlobals($context)
    {
        foreach ($this->globals as $key => $value)
        {
            if (!\array_key_exists($key, $context))
            {
                $context[$key] = $value;
            }
        }

        return $context;
    }

	public function startRender()
	{
		++$this->renderRequestLevel;
	}

	public function endRender()
	{
		if ($this->renderRequestLevel === 0)
		{
			// it is time to echo

			// final call event
			Context::getEventManager()->send(new Event('main', 'onPageRender', [
				self::class => $this
			]));

			// build output
			$this->renderStack();

			// render stack
			foreach ($this->outputStack as $k => $item)
			{
				if ($item instanceof Response)
				{
					$this->outputStack[$k] = $item->render();
				}
			}

			// output
			echo join('', $this->outputStack);
		}
		else
		{
			--$this->renderRequestLevel;
		}
	}

	protected function renderStack()
	{
		$deferredStack = $this->deferredStack;

		$this->outputStack = array_map(function ($elem) use ($deferredStack) {
			if ($elem instanceof StackPointer)
			{
				if (isset($deferredStack[(string) $elem]))
				{
					return (string) $deferredStack[(string) $elem];
				}
				else
				{
					return '';
				}
			}
			elseif (is_array($elem))
			{
				$res = call_user_func($elem);
				return $res;
			}
			else
			{
				return $elem;
			}
		}, $this->outputStack);
	}

	public function reset()
	{
		$this->deferredStack = $this->outputStack = [];
		$this->renderRequestLevel = -1;
	}
}