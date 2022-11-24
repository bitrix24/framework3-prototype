<?php

namespace Bitrix\Main\Lib\Controllers;

class Extension
{
    public function getJs()
    {
        return [];
    }

    public function getCss()
    {
        return [];
    }

    public function getAssets()
    {
        $assets = [];

        $reflector = new \ReflectionClass($this);
        $dirInProject = dirname($reflector->getFileName());
        $dirInProject = str_replace(PROJECT_ROOT, '', $dirInProject);

        // get also dependencies

        foreach ($this->getJs() as $jsAsset)
        {
            $assets[] = $dirInProject.'/'.$jsAsset;
        }

        foreach ($this->getCss() as $cssAsset)
        {
            $assets[] = $dirInProject.'/'.$cssAsset;
        }

        return $assets;
    }
}