<?php
namespace Twoeg;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Twig_Extension;

use rex_addon;
use rex_dir;
use rex_i18n;

class Twoeg
{
	protected $loader;
	protected $twig;

	protected $template_folder = null;
	protected $cache_folder = null;

	protected $twig_extension = [];
	protected $twig_filter = [];
	protected $twig_function = [];

	public function __construct(array $attributes = [])
	{
		foreach($attributes as $var => $value)
		{
			$method = 'set' . ucfirst(camel_case($var));

			if(method_exists($this, $method))
			{
				$this->$method($value);
			}
		}

		$twig = $this->getTwig();
	}

	protected function getTwig()
	{
		if($this->twig == null)
		{
			if($twig = new Twig_Environment($this->getLoader(), array('cache' => $this->getCacheFolder())))
			{
				if($twig = $this->initTwigFilters($twig))
				{
					if($twig = $this->initTwigFunctions($twig))
					{
						if($twig = $this->initTwigExtensions($twig))
						{
							$this->twig = $twig;
						}
					}
				}
			}
		}

		return $this->twig;
	}

	protected function getLoader()
	{
		if($this->loader == null)
		{
			if($loader = new Twig_Loader_Filesystem($this->getTemplateFolder()))
			{
				$this->loader = $loader;
			}
		}

		return $this->loader;
	}

	protected function initTwigFilters(Twig_Environment $twig)
	{
		foreach($this->getTwigFilters() as $filter)
		{
			$this->addTwigFilter($filter, $twig);
		}

		return $twig;
	}

	public function addTwigFilter(Twig_SimpleFilter $filter, Twig_Environment $twig = null)
	{
		if(empty($twig))
		{
			if(!empty($this->twig))
			{
				$this->twig->addFilter($filter);
				return $this->twig;
			}
		}
		else
		{
			$twig->addFilter($filter);

			return $twig;
		}

		return false;
	}

	protected function getTwigFilters()
	{
		// define all filters we want to use here.
		$filters = [];

		// rex_i18n::translate
		$filters[] = new Twig_SimpleFilter('translate', function ($string) {
    		return rex_i18n::translate($string);
		});

		// rex_i18n::msg
		$filters[] = new Twig_SimpleFilter('msg', function ($string, array $arguments = []) {
			return call_user_func_array(array('rex_i18n', 'msg'), array_merge(array($string), $arguments));
		}, array('is_variadic' => true));

		// any rexXXX::getYYY() method
		$filters[] = new Twig_SimpleFilter('get*', function ($name, $class = null) {
			$method = 'get' . $name;

			if(is_object($class) && substr(get_class($class), 0, 3) == 'rex')
			{
				if(method_exists($class, $method))
				{
					$arguments = func_get_args() > 2 ? array_slice(func_get_args(), 2) : [];

					return call_user_func_array(array($class, $method), $arguments);
				}
			}

			return null;
		});

		// any rexXXX::hasYYY() method
		$filters[] = new Twig_SimpleFilter('has*', function ($name, $class = null) {
			$method = 'has' . $name;

			if(is_object($class) && substr(get_class($class), 0, 3) == 'rex')
			{
				if(method_exists($class, $method))
				{
					$arguments = func_get_args() > 2 ? array_slice(func_get_args(), 2) : [];

					return call_user_func_array(array($class, $method), $arguments);
				}
			}

			return null;
		});

		return array_merge($filters, $this->twig_filter);
	}

	protected function setTwigFilter($filters)
	{
		$filters = !is_array($filters) ? [$filters] : $filters;

		foreach($filters as $filter)
		{
			if($filter instanceof Twig_SimpleFilter)
			{
				$this->twig_filter[] = $filter;
			}
		}

		return $this->twig_filter;
	}

	protected function initTwigFunctions(Twig_Environment $twig)
	{
		foreach($this->getTwigFunctions() as $function)
		{
			$this->addTwigFunction($function, $twig);
		}

		return $twig;
	}

	public function addTwigFunction(Twig_SimpleFunction $function, Twig_Environment $twig = null)
	{
		if(empty($twig))
		{
			if(!empty($this->twig))
			{
				$this->twig->addFunction($function);
				return $this->twig;
			}
		}
		else
		{
			$twig->addFunction($function);
			return $twig;
		}

		return false;
	}

	protected function getTwigFunctions()
	{
		$functions = [];

		// rex_i18n::msg
		$functions[] = new Twig_SimpleFunction('rex*', function ($name) {
			$class = 'rex' . $name;

			# rex::getUser()→hasPerm(‘myperm[]’)
			# turns to:
			# rex::getUser()|hasPerm(‘myperm[]’)
			if(($p = strpos($class, '__')) > 0)
			{
				$method = substr($class, $p+2);
				$class = substr($class, 0, $p);

				if(class_exists($class))
				{
					if(method_exists($class, $method))
					{
						$arguments = func_get_args() > 1 ? array_slice(func_get_args(), 1) : [];

						return call_user_func_array(array($class, $method), $arguments);
					}
				}
			}

			return '';
		});

		return array_merge($functions, $this->twig_function);
	}

	protected function setTwigFunction($functions)
	{
		$functions = !is_array($functions) ? [$functions] : $functions;

		foreach($functions as $function)
		{
			if($function instanceof Twig_SimpleFunction)
			{
				$this->twig_function[] = $function;
			}
		}

		return $this->twig_function;
	}

	protected function initTwigExtensions(Twig_Environment $twig)
	{
		foreach($this->getTwigExtensions() as $extension)
		{
			$this->addTwigExtension($extension, $twig);
		}

		return $twig;
	}

	public function addTwigExtension(Twig_Extension $extension, Twig_Environment $twig = null)
	{
		if(empty($twig))
		{
			if(!empty($this->twig))
			{
				$this->twig->addExtension($extension);
				return $this->twig;
			}
		}
		else
		{
			$twig->addExtension($extension);
			return $twig;
		}

		return false;
	}

	protected function getTwigExtensions()
	{
		$extensions = [];

		// Twig extensions
		// $extensions[] = new \Twig_Extensions_Extension_Intl();

		return array_merge($extensions, $this->twig_extension);
	}

	protected function setTwigExtension($extensions)
	{
		$extensions = !is_array($extensions) ? [$extensions] : $extensions;

		foreach($extensions as $extension)
		{
			$extension = ucfirst(strtolower((string) $extension));
			if(class_exists($extension = "\\Twig_Extensions_Extension_" . $extension))
			{
				$this->twig_extension[] = new $extension;
			}
		}

		return $this->twig_extension;
	}

	public function __call($method, $args) {

		if(method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), $args);	
		}
		else if($this->twig instanceof Twig_Environment)
		{
			return call_user_func_array(array($this->twig, $method), $args);	
		}
		else
		{
			throw new Exception('Method %s does not exist', get_called_class($this) . '::' . $method);
		}

	}

	public function getTemplateFolder()
	{
		return $this->getFolder('template');
	}

	public function setTemplateFolder($folder)
	{
		$default_folder = $this->getTemplateFolder();
		if($folder != $default_folder)
		{
			$folder = (array) $folder;
			$folder[] = $default_folder;
		}

		$this->template_folder = $folder;
	}

	public function getCacheFolder()
	{
		return $this->getFolder('cache');
	}

	public function setCacheFolder($folder)
	{
		$this->cache_folder = $folder;
	}

	protected function getFolder($type)
	{
		$type = (string) $type;
		$var = $type . '_folder';

		if(!property_exists ($this , $var ))
		{
			return null;
		}

		if($this->$var === null)
		{
			// set default template folder
			$dir = self::addon()->getProperty($var);

			if(empty($dir))
			{
				$dir = self::addon()->getDataPath($type);
			}

			$dir = trim($dir);

			if(!file_exists($dir) && !rex_dir::create($dir, true))
			{
				throw new Exception(sprintf("%1$s's' directory (%2$s) does not exist and could not be created.", $type, $dir));
			}
			else if(!is_dir($dir))
			{
				throw new Exception(sprintf("%1$s does not refer to a directory (%2$s).", $type, $dir));
			}
			else if(!is_readable($dir))
			{
				throw new Exception(sprintf("%1$s's directory (%2$s) is not readable.", $type, $dir));
			}

			$this->$var = $dir;
		}

		unset($dir);

		return $this->$var;
	}

	protected static function config()
	{
		return rex_file::getConfig(rex_path::addon('soervey', 'config.yml'));
	}

	protected static function addon()
	{
		return rex_addon::get('twoeg');
	}

	public static function render($template, array $variables = [], $settings = [])
	{
		$twoeg = new self($settings);
		$template = $twoeg->loadTemplate($template);
		return $template->render($variables);
	}

	public static function out($template, array $variables = [], $settings = [])
	{
		echo self::render($template, $variables, $settings);
	}
}