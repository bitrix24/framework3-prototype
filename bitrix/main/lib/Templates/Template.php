<?php

namespace Bitrix\Main\Lib\Templates;

class Template
{
    public $router;

    public function __construct($routerContext = null)
    {
        $this->router = new TemplateRouter($routerContext);
    }

}