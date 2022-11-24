<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2022 Bitrix
 */

namespace Bitrix\Main\Lib\Routing;

use Bitrix\Main\Lib\Exceptions\ArgumentException;
use Bitrix\Main\Lib\Routing\Controllers\Controller;
use Bitrix\Main\Lib\Type\ParameterDictionary;

/**
 * @package    bitrix
 * @subpackage main
 */
class Route
{
	/** @var string Defined by user */
	protected $uri;

	/** @var string uri with prefix */
	protected $fullUri;

	/** @var string Defined by compile() */
	protected $matchUri;

	/** @var array [name => pattern] Defined by compile() */
	protected $parameters;

	/** @var ParameterDictionary Set by router->match() */
	protected $parametersValues;

	protected Controller $controller;

	/** @var Options */
	protected $options;

	/** @var Options[] */
	protected $customOptions;

	protected $parameterAliases = [];

	public function __construct($uri, Controller $controller)
	{
		$this->uri = '/' . ltrim($uri, '/');
		$this->controller = $controller;

		// set itself to controller
		$this->controller->setRoute($this);
	}

	/**
	 * @return Options
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @param Options $options
	 */
	public function setOptions($options)
	{
		$this->options = $options;

		// clear runtime cache
		$this->fullUri = null;
		$this->matchUri = null;
	}

	public function getCustomOptions($scopeName)
	{
		if (empty($this->customOptions[$scopeName]))
		{
			throw new ArgumentException(sprintf(
			   'Custom options `%s` not found', $scopeName
			));
		}

		return $this->customOptions[$scopeName];
	}

	public function hasCustomOptions($scopeName)
	{
		return !empty($this->customOptions[$scopeName]);
	}

	public function addCustomOptions($scopeName, Options $options)
	{
		$this->customOptions[$scopeName] = $options;
	}

	/**
	 * @param Options[] $customOptions
	 */
	public function setCustomOptions(array $customOptions): void
	{
		$this->customOptions = $customOptions;
	}

	/**
	 * @return Controller
	 */
	public function getController() : Controller
	{
		return $this->controller;
	}

	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * @return ParameterDictionary
	 */
	public function getParametersValues()
	{
		if ($this->parametersValues === null)
		{
			$this->parametersValues = new ParameterDictionary;
		}

		return $this->parametersValues;
	}

	public function getParameterValue($name)
	{
		return $this->getParametersValues()->get($name);
	}

	protected function getAliasByParameterName($parameterName)
	{
		if (empty($this->parameterAliases[$parameterName]))
		{
			$alias = 'pAlias' . count($this->parameterAliases);
			$this->parameterAliases[$parameterName] = $alias;
		}

		return $this->parameterAliases[$parameterName];
	}

	protected function getParameterNameByAlias($alias)
	{
		return array_search($alias, $this->parameterAliases);
	}

	public function compile($hasTree = false)
	{
		if ($this->matchUri !== null)
		{
			return;
		}

		$this->matchUri = $hasTree
			? "#^{$this->getUri()}(?<__TAIL__>.*)#"
			: "#^{$this->getUri()}$#";

		$this->parameters = [];

		// there are parameters, collect them
		preg_match_all('/{([a-z0-9_:]+)}/i', $this->getUri(), $matches);
		$parameterNames = $matches[1];

		foreach ($parameterNames as $parameterName)
		{
			$pattern = null;

			// check options for custom pattern
			if ($this->options)
			{
				if ($this->options->hasWhere($parameterName))
				{
					// custom pattern
					$pattern = $this->options->getWhere($parameterName);
				}
				elseif ($this->options->hasDefault($parameterName))
				{
					// can be empty
					$pattern = '[^/]*';
				}
			}

			if ($pattern === null)
			{
				// general case
				$pattern = '[^/]+';
			}

			$this->parameters[$parameterName] = $pattern;

			//$parameterInPatternName = $this->options->getFullName().':'.$parameterName;
			$parameterInPatternName = $this->getAliasByParameterName($parameterName);

			// put pattern in uri
			$this->matchUri = str_replace(
				"{{$parameterName}}",
				"(?<{$parameterInPatternName}>{$pattern})",
				$this->matchUri
			);
		}
	}

	public function compileFromCache($cacheData)
	{
		$this->matchUri = $cacheData['matchUri'];
		$this->parameters = $cacheData['parameters'];
	}

	public function getCompileCache()
	{
		$this->compile();

		return [
			'matchUri' => $this->matchUri,
			'parameters' => $this->parameters
		];
	}

	public function match($uriPath, $request, $requestHandler)
	{
		$hasTree = false;

		if (strpos($this->getUri(), '{') !== false)
		{
			// compile regexp with hasTree option
			$this->compile($hasTree);

			// match
			$result = preg_match($this->matchUri, $uriPath, $matches);

			if ($result)
			{
				// set parameters to the request
				$requestParameters = [];
				$parametersList = array_keys($this->parameters);

				foreach ($parametersList as $parameter)
				{
					$matchedParameterName = $this->getAliasByParameterName($parameter);

					if ($matches[$matchedParameterName] === '' && $this->options && $this->options->hasDefault($parameter))
					{
						// set default value if optional parameter is empty
						$requestParameters[$parameter] = $this->options->getDefault($parameter);
					}
					else
					{
						$requestParameters[$parameter] = $matches[$matchedParameterName];
					}

					// duplicate value with original parameters name
					// to be more accurate could only duplicate params from $this->uri
					$parameterParts = explode(Options::COMPLEX_ROUTE_SEPARATOR, $parameter);
					$originalParameterName = end($parameterParts);

					$requestParameters[$originalParameterName] = $requestParameters[$parameter];
				}

				// set default values if parameter with the same name wasn't set in request
				// e.g. "RULE" => "download=1&objectId=\$1"
				if (!empty($defaultValues = $this->options->getDefault()))
				{
					foreach ($defaultValues as $parameter => $defaultValue)
					{
						if (!in_array($parameter, $parametersList))
						{
							$requestParameters[$parameter] = $defaultValue;
						}
					}
				}

				return $requestParameters;
			}
		}
		else
		{
			// exact match
			if ($uriPath === $this->getUri())
			{
				$requestParameters = [];

				// set default values if parameter with the same name wasn't set in request
				// e.g. "RULE" => "download=1&objectId=\$1"
				if (!empty($defaultValues = $this->options->getDefault()))
				{
					foreach ($defaultValues as $parameter => $defaultValue)
					{
						$requestParameters[$parameter] = $defaultValue;
					}
				}

				return $requestParameters ?: true;
			}
		}

		return false;
	}

	function getUri()
	{
		if ($this->fullUri === null)
		{
			$this->fullUri = $this->uri;

			// concat with option prefix and cache
			if ($this->options && $this->options->hasPrefix())
			{
				$this->fullUri = $this->options->getFullPrefix() . $this->uri;
			}
		}

		return $this->fullUri;
	}
}