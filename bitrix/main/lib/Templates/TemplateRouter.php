<?php

namespace Bitrix\Main\Lib\Templates;

use Bitrix\Main\Lib\Context;
use Bitrix\Main\Lib\Routing\Options;

class TemplateRouter
{
    protected $context;

    public function __construct($context = null)
    {
        $this->context = $context;
    }

    public function linkTo($routeName, $routeParameters)
    {
        $currentRoute = Context::getRoute();

        $allOptions = [];

        // custom routing configuration
        if (!empty($this->context['configuration']))
        {
            $allOptions[] = $currentRoute->getCustomOptions($this->context['configuration']);
        }

        $allOptions[] = $currentRoute->getOptions();

        // build fully qualified names
        $routeParentNames = $routeNames = $currentRoute->getOptions()->getParentNames();
        $routeNames[] = $routeName;

        //$routeParentFqn = join(Options::COMPLEX_ROUTE_SEPARATOR, $routeParentNames);
        $routeFqn = join(Options::COMPLEX_ROUTE_SEPARATOR, $routeNames);

        $uri = null;

        foreach ($allOptions as $options)
        {
            // check for an alias
            if (!empty($options->getRouteBinding()[$routeName]))
            {
                return Context::getRouter()->route($options->getRouteBinding()[$routeName], $routeParameters);
            }

            // parameters with fqn
//        $parameters = [];
//
//        foreach ($routeParameters as $localParameter => $parameterValue)
//        {
//            $parameterFqn = $routeParentFqn . Options::COMPLEX_ROUTE_SEPARATOR . $localParameter;
//            $parameters[$parameterFqn] = $parameterValue;
//
//            // duplicate original keys if they are "absolute" in context of complex structure
//            $parameters[$localParameter] = $parameterValue;
//        }
//            $parameters = $routeParameters;

            // search for aliased solutions
            foreach ($options->getSolutionBinding() as $solutionAlias)
            {
                $routeAlias = $solutionAlias . Options::COMPLEX_ROUTE_SEPARATOR . $routeName;

                if (Context::getRouter()->hasRouteByName($routeAlias))
                {
                    $uri = Context::getRouter()->route($routeAlias, $routeParameters);
                    break 2;
                }
            }

            if ($uri === null)
            {
                // final uri
                if (Context::getRouter()->hasRouteByName($routeFqn))
                {
                    // relative route
                    $uri = Context::getRouter()->route($routeFqn, $routeParameters);
                    break;
                }
                elseif (Context::getRouter()->hasRouteByName($routeName))
                {
                    // absolute route
                    $uri = Context::getRouter()->route($routeName, $routeParameters);
                    break;
                }
            }
        }

        if ($uri === null)
        {
            throw new \Exception('Route `'.$routeName.'` not found');
        }

        return $uri;
    }
}