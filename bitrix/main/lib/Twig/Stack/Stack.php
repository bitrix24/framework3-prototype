<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Twig\Stack;


class Stack extends \ArrayObject
{
    protected $separator = '';

    public function push($content, $prepend = false)
    {
        $prepend
            ? $this->prepend($content)
            : $this->append($content);
    }

    public function prepend($value)
    {
        $array = $this->getArrayCopy();
        array_unshift($array, $value);
        $this->exchangeArray($array);
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    public function __toString()
    {
        return join($this->separator, $this->getArrayCopy());
    }
} 