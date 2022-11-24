<?php

namespace Bitrix\Main\Lib;

use Bitrix\Main\Lib\Twig\Stack\Extension\StackExtension;

class AssetManager
{
    protected $assets = [];

    public function addAsset($asset)
    {
        if (!isset($this->assets[$asset]))
        {
            $this->assets[$asset] = true;
        }
    }

    public function render()
    {
        $env = Response::getTwigEnvironment();
        $stackExt = $env->getExtension(StackExtension::class);

        $output = '';

        foreach (array_keys($this->assets) as $asset)
        {
            // identify asset
            $path = $this->getResourcePath($asset);

            if (substr($asset, -3) === 'css')
            {
                $output .= '<link href="'.$path.'" type="text/css" rel="stylesheet">'."\n";
            }
            elseif (substr($asset, -2) === 'js')
            {
                $output .= '<script type="text/javascript" src="'.$path.'"></script>'."\n";
            }
        }

        if ($output !== '')
        {
            //$stackExt->pushStack('header', $output.PHP_EOL);
        }

        return $output;
    }

    public function getResourcePath($path)
    {
        $uri = '/resources/';

        $resourceHash = null;
        $resourcePath = $path;
        $resourcePathParts = explode('/', ltrim($resourcePath, '/'));

        // guess it is module
        // the is no "modules" at the moment
        if ($resourcePathParts[1] === 'modules' && !empty($resourcePathParts[2]))
        {
            $modulePath = $resourcePathParts[0].'/'.$resourcePathParts[1].'/'.$resourcePathParts[2];
            $resourceMetaPath = PROJECT_ROOT.'/'.$modulePath.'/meta/resource.map';

            if (file_exists($resourceMetaPath))
            {
                // it is module with meta, get resource hash
                $resourceMeta = new ResourceMeta($resourceMetaPath);
                $resourceRelPath = join('/', array_slice($resourcePathParts, 3));
                $resourceHash = $resourceMeta->getHash($resourceRelPath);

                if ($resourceHash !== null)
                {
                    // rewrite filename
                    $resourceFilename = basename($resourcePath);

                    $lastDotPos = strrpos($resourceFilename, '.');
                    $resourceName = substr($resourceFilename, 0, $lastDotPos);
                    $resourceExt = substr($resourceFilename, $lastDotPos + 1);

                    $staticResourceFilename = $resourceName.'.'.$resourceHash.'.'.$resourceExt;
                    $resourcePathParts[count($resourcePathParts)-1] = $staticResourceFilename;

                    // return uri with hash
                    $path = join('/', $resourcePathParts);
                }
                else
                {
                    // count runtime
                    // trigger warning
                }
            }
        }


        if ($resourceHash === null)
        {
            $resourceAbsPath = PROJECT_ROOT.'/'.ltrim($path, '/');

            // get resource hash from cache
            $resourceHashMap = [];
            $cache = Context::getCache();
            $item = $cache->getItem('RESOURCES_HASH_RUNTIME');

            if ($item->isHit())
            {
                $resourceHashMap = $item->get();

                if (isset($resourceHashMap[$path]))
                {
                    // found resource hash
                    $resourceHash = $resourceHashMap[$path];
                }
            }

            if ($resourceHash === null)
            {
                // count runtime
                $resourceHash = substr(md5_file($resourceAbsPath), 0, 12);

                // put in cache
                $resourceHashMap[$path] = $resourceHash;
                $item->set($resourceHashMap);
                $item->expiresAfter(3600*24*365);
                $cache->saveDeferred($item);
            }

            // rewrite filename
            $resourceFilename = basename($resourcePath);

            $lastDotPos = strrpos($resourceFilename, '.');
            $resourceName = substr($resourceFilename, 0, $lastDotPos);
            $resourceExt = substr($resourceFilename, $lastDotPos + 1);

            $staticResourceFilename = $resourceName.'.'.$resourceHash.'.'.$resourceExt;
            $resourcePathParts[count($resourcePathParts)-1] = $staticResourceFilename;

            // return uri with hash
            $path = join('/', $resourcePathParts);
        }

        return $uri.$path;
    }
}