#!/usr/bin/env php
<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL */

namespace il\CodeModifier;

/**
 * Class ClassInfo
 * @package il\CodeModifier
 * @author  Michael Jansen <mjansen@databay.de>
 * @author  Alex Killing <killing@leifos.de>
 */
class ClassInfo
{
	static $include_tokens = false;
	static $debug = false;
	static $stop_at = 10;

	static $dic_functions = array(
		"ilDB" => "database()",
		"ilCtrl" => "ctrl()",
		"ilUser" => "user()",
		"ilAccess" => "access()",
		"tree" => "repositoryTree()",
		"lng" => "language()",
		"ilToolbar" => "toolbar()",
		"ilTabs" => "tabs()",
		"ilSetting" => "settings()",
		"rbacsystem" => "rbac()->system()",
		"rbacadmin" => "rbac()->admin()",
		"rbacreview" => "rbac()->review()"
	);

	public static $globals = array(
		"DIC" => array(
			"property" => "dic",
			"type" => '\ILIAS\DI\Container'
		),
		"ilDB" => array(
			"property" => "db",
			"type" => "ilDB"
		),
		"ilCtrl" => array(
			"property" => "ctrl",
			"type" => "ilCtrl"
		),
		"ilUser" => array(
			"property" => "user",
			"type" => "ilObjUser"
		),
		"ilAccess" => array(
			"property" => "access",
			"type" => "ilAccessHandler"
		),
		"tree" => array(
			"property" => "tree",
			"type" => "ilTree"
		),
		"lng" => array(
			"property" => "lng",
			"type" => "ilLanguage"
		),
		"ilToolbar" => array(
			"property" => "toolbar",
			"type" => "ilToolbarGUI"
		),
		"ilTabs" => array(
			"property" => "tabs",
			"type" => "ilTabsGUI"
		),
		"ilSetting" => array(
			"property" => "settings",
			"type" => "ilSetting"
		),
		"rbacsystem" => array(
			"property" => "rbacsystem",
			"type" => "ilRbacSystem"
		),
		"rbacadmin" => array(
			"property" => "rbacadmin",
			"type" => "ilRbacAdmin"
		),
		"rbacreview" => array(
			"property" => "rbacreview",
			"type" => "ilRbacReview"
		),
		"ilIliasIniFile" => array(
			"property" => "ilias_ini",
			"type" => "ilIniFile"
		),
		"ilClientIniFile" => array(
			"property" => "client_ini",
			"type" => "ilIniFile"
		),
		"styleDefinition" => array(
			"property" => "style_definition",
			"type" => "ilStyleDefinition"
		),
		"ilCollator" => array(
			"property" => "collator",
			"type" => "Collator"
		),
		"ilAuthSession" => array(
			"property" => "auth_session",
			"type" => "ilAuthSession"
		),
		"ilLog" => array(
			"property" => "log",
			"type" => "Logger",
		),
		"ilErr" => array(
			"property" => "error",
			"type" => "ilErrorHandling"
		),
		"ilPluginAdmin" => array(
			"property" => "plugin_admin",
			"type" => "ilPluginAdmin"
		),
		"objDefinition" => array(
			"property" => "obj_definition",
			"type" => "ilObjectDefinition"
		),
		"tpl" => array(
			"property" => "tpl",
			"type" => "ilTemplate"
		),
		"ilNavigationHistory" => array(
			"property" => "nav_history",
			"type" => "ilNavigationHistory"
		),
		"ilHelp" => array(
			"property" => "help",
			"type" => "ilHelpGUI"
		),
		"ilLocator" => array(
			"property" => "locator",
			"type" => "ilLocatorGUI"
		),
		"ilMainMenu" => array(
			"property" => "main_menu",
			"type" => "ilMainMenuGUI"
		),
		"ilBrowser" => array(
			"property" => "browser",
			"type" => "ilBrowser"
		),
		"ilObjDataCache" => array(
			"property" => "obj_data_cache",
			"type" => "ilObjectDataCache"
		),
		"ilAppEventHandler" => array(
			"property" => "app_event_handler",
			"type" => "ilAppEventHandler"
		)
	);

	/**
	 * Get dic accessor
	 *
	 * @param
	 * @return
	 */
	static function getDicAccessor($a_global)
	{
		if (isset(self::$dic_functions[$a_global]))
		{
			return '$DIC->'.self::$dic_functions[$a_global];
		}
		else
		{
			return '$DIC["'.$a_global.'"]';
		}
	}

	/**
	 * Get property for global
	 *
	 * @param
	 * @return
	 */
	function getPropertyForGlobal($g)
	{
		return self::$globals[$g]["property"];
	}



	/**
	 * @var string
	 */
	protected $path = '';

	/**
	 * @var string
	 */
	protected $class = '';

	/**
	 * @var array
	 */
	protected $methods = array();

	/**
	 * ClassInfo constructor.
	 * @param $path    string
	 * @param $class   string
	 */
	public function __construct($path, $class)
	{
		$this->path  = $path;
		$this->class = $class;
	}

	/**
	 * @param string $method
	 * @param int    $line
	 */
	public function pushMethod($method, $line)
	{
		$this->methods[self::normalize($method)] = $line;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * @param $string
	 * @return string
	 */
	protected static function normalize($string)
	{
		return strtolower($string);
	}

	/**
	 * Analyse method
	 *
	 * @param
	 * @return
	 */
	static function analyseMethod($a_method, $a_file_content)
	{
		$method_code_array = array_slice(explode("\n", $a_file_content), $a_method->getStartLine() - 1,
			$a_method->getEndLine() - $a_method->getStartLine() + 1);
		$method_code_str = implode("\n", $method_code_array);



		$method_tokens = token_get_all("<?php ".$method_code_str);

		// analyse statements
		$statements = array();
		$c_statement = "";
		$is_global = false;
		$is_assignment = false;
		foreach ($method_tokens as $token)
		{
			if (in_array($token, array("{", "}", ";")))
			{
				if (trim($c_statement != ""))
				{
					$statements[] = array(
						"statement" => trim($c_statement),
						"is_assignment" => $is_assignment,
						"is_global" => $is_global
					);
					$c_statement = "";
					$is_global = false;
					$is_assignment = false;
				}
			}
			else
			{
				if (isset($token[1]))
				{
					if ($token[1] == "global")
					{
						$is_global = true;
					}
					if (!in_array($token[0], array(T_COMMENT, T_DOC_COMMENT)))	// see http://php.net/manual/de/tokens.php
					{
						$c_statement .= $token[1];
					}
				}
				if (is_string($token))
				{
					if ($token == "=")
					{
						$is_assignment = true;
					}
					$c_statement.= $token;
				}
			}
		}

		// collect all globals in this method
		$globals = array();
		foreach ($statements as $st)
		{
			if ($st["is_global"])
			{
				foreach (explode(",", substr($st["statement"], 7)) as $g)
				{
					$globals[] = substr(trim($g), 1);
				}
			}
		}

		// collect all globals to class member assignments
		$member_to_global = array();
		$dic_to_global = array();
		foreach ($statements as $st)
		{
			if ($st["is_assignment"])
			{
				$eqpos = strpos($st["statement"], "=");
				$right = trim(substr($st["statement"], $eqpos + 1));
				$mem_var = trim (substr($st["statement"], 7, $eqpos - 7));
				if ((substr($st["statement"], 0, 7) == '$this->') &&
					substr($right, 0, 1) == '$' &&
					in_array(substr($right, 1), $globals))
				{
					$member_to_global[substr($right, 1)] = $mem_var;
				}

				if ((substr($st["statement"], 0, 7) == '$this->') &&
					substr($right, 0, 4) == '$DIC')
				{
					foreach (self::$globals as $k => $g)
					{
						if (is_int(strpos($right, "'".$k."'")) || is_int(strpos($right, '"'.$k.'"')))
						{
							$dic_to_global[$k] = $mem_var;
						}
					}
					foreach (self::$dic_functions as $k => $f)
					{
						if (is_int(strpos($right, $f)))
						{
							$dic_to_global[$k] = $mem_var;
						}
					}
				}

				//if (is_int(strpos($st["statement"], '$ilCtrl')))
				/*
				if (count($dic_to_global) > 0)
				{
					var_dump($st["statement"]);
					var_dump($eqpos);
					var_dump($right);
					var_dump(substr($st["statement"], 0, 7));
					var_dump($globals);
					var_dump($member_to_global);
					var_dump($dic_to_global);
					exit;
				}*/
			}
		}

		/*
		if (is_int(stripos($method_code_str, "global")) && count($globals) > 2)
		{
			var_dump($method_code_str);
			var_dump($method_tokens);
			var_dump($statements);
			var_dump($globals);
			exit;
		}*/

		$info = array(
			"name" => $a_method->name,
			"code_str" => $method_code_str,
			"statements" => $statements,
			"globals" => $globals,
			"member_to_global" => $member_to_global,
			"dic_to_global" => $dic_to_global
		);

		if (self::$include_tokens)
		{
			$info["tokens"] = $method_tokens;
		}
		return $info;
	}

	/**
	 * Collect globals
	 *
	 * @param
	 */
	function collectGlobals(&$globals, $method_info)
	{
		if (is_array($method_info["globals"]))
		{
			foreach ($method_info["globals"] as $g)
			{
				if (!in_array($g, $globals))
				{
					$globals[] = $g;
				}
			}
		}
	}


	/**
	 * @param string $file_path
	 * @return \Generator
	 */
	public static function parseClassInfosFromFile($file_path)
	{
		include_once($file_path);
		$class_name = explode(".", basename($file_path));
		$class_name = $class_name[count($class_name) - 2];
		$rclass = new \ReflectionClass($class_name);

		$file_content = file_get_contents($file_path);

		$constructor = null;
		$constructor_info = null;
		$class_globals = [];

		if ($rclass->getConstructor() != null && $rclass->getConstructor()->getDeclaringClass()->name == $rclass->name)
		{
			$constructor = $rclass->getConstructor();
			$constructor_info = self::analyseMethod($constructor, $file_content);
			self::collectGlobals($class_globals, $constructor_info);
		}

		$method_info  = [];
		$methods = [];
		foreach ($rclass->getMethods() as $m)
		{
			if ($m->getDeclaringClass()->name == $rclass->name)
			{
				$mi = self::analyseMethod($m, $file_content);
				$method_info[] = $mi;
				if (!$m->isStatic())
				{
					self::collectGlobals($class_globals, $mi);
				}
				$methods[$m->name] = $m;
			}
		}

		yield array(
			"path" => $file_path,
			"file_content" => $file_content,
			"class_nane" => $class_name,
			"methods" => $methods,
			"constructor" => $constructor,
			"constructor_info" => $constructor_info,
			"methods_info" => $method_info,
			"all_non_static_globals" => $class_globals,
			"class" => $rclass
		);


		/*
		$tokens     = token_get_all(file_get_contents($file_path));
		$num_tokens = count($tokens);

		$last_class = null;
		$namespace  = '';

		for ($i = 0; $i < $num_tokens; $i++) {
			if (is_string($tokens[$i])) {
				continue;
			}

			$token = $tokens[$i][0];
			$line  = $tokens[$i][2];
			switch ($token) {
				case T_NAMESPACE:
					$namespace = self::getNamespaceName($tokens, $i);
					break;

				case T_CLASS:
					if ($last_class) {
						yield $last_class;
					}

					$class_name = self::getClassName($namespace, $tokens, $i);
					$last_class = new self($file_path, $class_name);
					break;

				case T_FUNCTION:
					$function_name = null;

					if (is_array($tokens[$i + 2]) && $tokens[$i + 2][0] == T_STRING) {
						$function_name = $tokens[$i + 2][1];
					} else if ($tokens[$i + 2] == '&' && is_array($tokens[$i + 3]) && $tokens[$i + 3][0] == T_STRING) {
						$function_name = $tokens[$i + 3][1];
					}

					if ($function_name && $last_class) {
						$last_class->pushMethod($function_name, $line);
					}
			}
		}

		if ($last_class) {
			yield $last_class;
		}*/
	}

	/**
	 * @param array $tokens
	 * @param int   $i
	 * @return bool|string
	 */
	protected static function getNamespaceName(array $tokens, $i)
	{
		if (isset($tokens[$i + 2][1])) {
			$namespace = $tokens[$i + 2][1];

			for ($j = $i + 3; ; $j += 2) {
				if (isset($tokens[$j]) && $tokens[$j][0] == T_NS_SEPARATOR) {
					$namespace .= '\\' . $tokens[$j + 1][1];
				} else {
					break;
				}
			}

			return $namespace;
		}

		return false;
	}

	/**
	 * @param string $namespace
	 * @param array  $tokens
	 * @param int    $i
	 * @return string
	 */
	protected static function getClassName($namespace, array $tokens, $i)
	{
		$i += 2;
		$namespaced = false;
		$class_name = $tokens[$i][1];

		if ($class_name === '\\') {
			$namespaced = true;
		}

		while (is_array($tokens[$i + 1]) && $tokens[$i + 1][0] !== T_WHITESPACE) {
			$class_name .= $tokens[++$i][1];
		}

		if (!$namespaced && $namespace) {
			$class_name = $namespace . '\\' . $class_name;
		}

		return $class_name;
	}
}


/**
 * Class DicCodeModifier
 * @package il\CodeModifier
 * @author  Michael Jansen <mjansen@databay.de>
 */
class DicCodeModifier
{
	/**
	 * @var string
	 */
	const ILIAS_CLASS_FILE_RE = 'class\..*\.php$';

	protected $last_function = "";
	protected $current_function = "";
	protected $in_function = false;
	protected $first_line = false;
	protected $first_class_line = false;
	protected $first_line_late = false;
	protected $first_class_line_late = false;

	/**
	 * Get Line Info
	 *
	 * @param
	 * @return
	 */
	function getLineInfo(&$a_nr, $a_info, $line)
	{
		$a_nr++;
		$this->in_function = false;
		$this->first_line = false;
		$this->first_class_line = false;

		// determin first class line
		if ($this->first_class_line_late)
		{
			$this->first_class_line = true;
			$this->first_class_line_late = false;
		}
		if ($a_nr == $a_info["class"]->getStartLine() + 1)
		{
			if (trim($line) != "{")
			{
				$this->first_class_line = true;
			}
			else
			{
				$this->first_class_line_late = true;
			}
		}

		foreach ($a_info["methods"] as $m)
		{
			if ($a_nr >= $m->getStartLine() && $a_nr <= $m->getEndLine())
			{
				$this->in_function = true;
				if ($m->name != $this->current_function && $this->current_function != "")
				{
					$this->last_function = $this->current_function;
				}
				$this->current_function = $m->name;

				// determin first method line
				if ($this->first_line_late)
				{
					$this->first_line = true;
					$this->first_line_late = false;
				}
				if ($a_nr == $m->getStartLine() + 1)
				{
					if (trim($line) != "{")
					{
						$this->first_line = true;
					}
					else
					{
						$this->first_line_late = true;
					}
				}
			}
		}
	}

	/**
	 * At first line of method
	 *
	 * @param
	 * @return
	 */
	function atfirstLineOfMethod()
	{
		return $this->first_line;
	}

	/**
	 * in method
	 *
	 * @param
	 * @return
	 */
	function inMethod()
	{
		return $this->in_function;
	}

	/**
	 * At first line of class
	 *
	 * @param
	 * @return
	 */
	function atFirstLineOfClass()
	{
		return $this->first_class_line;
	}

	
	
	/**
	 * In constructor
	 *
	 * @return bool
	 */
	function inConstructor()
	{
		if ($this->current_function == "__construct")
		{
			return true;
		}
		return false;
	}

	/**
	 * Get current method
	 *
	 * @param
	 * @return
	 */
	function getCurrentMethod()
	{
		return $this->current_function;
	}

	/**
	 * Get class property name for global
	 *
	 * @param
	 * @return
	 */
	function getClassPropertyName($g, $a_info)
	{
		if ($a_info["constructor_info"]["member_to_global"][$g])
		{
			return $a_info["constructor_info"]["member_to_global"][$g];
		}
		if ($a_info["constructor_info"]["dic_to_global"][$g])
		{
			return $a_info["constructor_info"]["dic_to_global"][$g];
		}
		return ClassInfo::$globals[$g]["property"];
	}



	/**
	 * Modify class file
	 *
	 * @param array $a_info
	 * @return bool
	 */
	function modifyClass($a_info)
	{
		$line_marker = ClassInfo::$debug
			? "++"
			: "";

		// $a_info["path"];
		$new_lines = [];
		$orig_line_cnt = 0;
		foreach (explode("\n", $a_info["file_content"]) as $line)
		{
			$skip_line = false;

			$this->getLineInfo($orig_line_cnt, $a_info, $line);

			// class properties
			if ($this->atFirstLineOfClass())
			{
				$props = array_map(function($a) {return $a->name;}, $a_info["class"]->getProperties());
				foreach ($a_info["all_non_static_globals"] as $g)
				{
					if ($g != "DIC")
					{
						$prop_name = $this->getClassPropertyName($g, $a_info);
						if (!in_array($prop_name, $props))
						{
							$new_lines[] = $line_marker . '	/**';
							$new_lines[] = $line_marker . '	 * @var ' . ClassInfo::$globals[$g]["type"];
							$new_lines[] = $line_marker . '	 */';
							$new_lines[] = $line_marker . '	protected $' . $prop_name . ';';
							$new_lines[] = $line_marker . '';
						}
					}
				}
			}

			// constructor DIC handling
			$new_constr_lines = array();
			if (($this->inConstructor() && $this->atFirstLineOfMethod()) ||
				($this->atFirstLineOfClass() && $a_info["constructor"] == null))
			{
				$globals = array();
				if (substr(trim($line), 0, 7) == "global ")
				{
					$globals = explode(",", str_replace(";", "", substr(trim($line), 7)));
				}
				if (count($a_info["all_non_static_globals"]) > 0
					&& !(count($a_info["all_non_static_globals"]) == 1 && trim($a_info["all_non_static_globals"][0]) == 'DIC')
					&& !(count($globals) == 1 && trim($globals[0]) == '$DIC'))
				{
					$new_constr_lines[] = $line_marker . '		global $DIC;';
					$new_constr_lines[] = $line_marker . '';
					if (substr(trim($line), 0, 7) == "global ")
					{
						$skip_line = true;
					}
				}

				foreach ($a_info["all_non_static_globals"] as $g)
				{
					if (!isset($a_info["constructor_info"]["member_to_global"][$g]) && !isset($a_info["constructor_info"]["dic_to_global"][$g]))
					{
						if ($g != "DIC")
						{
							$new_constr_lines[] = $line_marker . '		$this->' . ClassInfo::getPropertyForGlobal($g) . " = " . ClassInfo::getDicAccessor($g) . ";";
						}
					}
				}
			}

			if (count($new_constr_lines) > 0)
			{
				if (($this->atFirstLineOfClass() && $a_info["constructor"] == null))
				{
					$new_lines[] = $line_marker.'';
					$new_lines[] = $line_marker.'	/**';
					$new_lines[] = $line_marker.'	 * Constructor';
					$new_lines[] = $line_marker.'	 */';
					$new_lines[] = $line_marker.'	function __construct()';
					$new_lines[] = $line_marker.'	{';
					if ($a_info["class"]->getParentClass() != null)
					{
						$new_lines[] = $line_marker.'		parent::__construct();';
					}
					//if ($a_info["class"]->getExtensions())
				}

				foreach ($new_constr_lines as $ncl)
				{
					$new_lines[] = $ncl;
				}

				if (($this->atFirstLineOfClass() && $a_info["constructor"] == null))
				{
					$new_lines[] = $line_marker.'	}';
					$new_lines[] = $line_marker.'';
				}
			}

			if ($this->inConstructor())
			{
				if (substr(trim($line), 0, 7) == "global ")
				{
					$globals = explode(",", str_replace(";", "", substr(trim($line), 7)));
					foreach ($globals as $g)
					{
						$g = str_replace('$', "", trim($g));
						if (!isset(ClassInfo::$globals[$g]))
						{
							die ("Unknown global found: $g");
						}
						if ($g != "DIC")
						{
							$new_lines[] = $line_marker . '		$' . $g . ' = ' . ClassInfo::getDicAccessor($g) . ";";
							$skip_line = true;
						}
					}
				}
			}

			// method DIC handling
			if (!$this->inConstructor())
			{
				if ($this->inMethod())
				{
					if (substr(trim($line), 0, 7) == "global ")
					{
						$globals = explode(",", str_replace(";", "", substr(trim($line), 7)));
						if (count($globals) > 0 && !(count($globals) == 1 && trim($globals[0]) == '$DIC'))
						{
							if ($a_info["methods"][$this->getCurrentMethod()]->isStatic())
							{
								$new_lines[] = $line_marker . '		global $DIC;';
								$new_lines[] = $line_marker . '';
							}
							$skip_line = true;
						}
						foreach ($globals as $g)
						{
							$g = str_replace('$', "", trim($g));
							if (!isset(ClassInfo::$globals[$g]))
							{
								die ("Unknown global found: $g");
							}
							if ($g != "DIC")
							{
								if ($a_info["methods"][$this->getCurrentMethod()]->isStatic())
								{
									$new_lines[] = $line_marker.'		$' . $g . ' = ' . ClassInfo::getDicAccessor($g).";";
								}
								else
								{
									$new_lines[] = $line_marker.'		$' . $g . ' = $this->'.$this->getClassPropertyName($g, $a_info).";";
								}
							}
						}
					}
				}
			}

			if (!$skip_line)
			{
				$new_lines[] = $line;
			}
			else if ($line_marker != "")
			{
				$new_lines[] = "--".$line;
			}
		}

		$new_file_content = implode("\n", $new_lines);

		if ($a_info["file_content"] != $new_file_content)
		{
			if (ClassInfo::$debug)
			{
				echo $new_file_content;
			}
			else
			{
				file_put_contents($a_info["path"], $new_file_content);
			}
			return true;
		}
		return false;
	}



	/**
	 * Main
	 * @param array $args
	 */
	public function run(array $args)
	{
		if (!isset($args[1])) {
			$this->printUsage();
		}

		$t0 = microtime(true);
		$modified = 0;
		try {
			foreach ($this->getIliasClasses($args[1]) as $class_info) {
				if (ClassInfo::$stop_at == 0 || ClassInfo::$stop_at > $modified)
				{
					if ($this->modifyClass($class_info))
					{
						$modified++;
					}
				}
			}
		} catch (\Exception $e) {
			echo $e->getMessage() . "\n";
		}
		echo sprintf("Execution time: %s seconds\n", (microtime(true) - $t0));
		echo sprintf("Memory usage (peak): %s bytes\n", memory_get_peak_usage(true));
	}

	/**
	 * @param $path string
	 * @return \Generator
	 */
	protected function getIliasClasses($path)
	{
		foreach ($this->getIliasClassFiles($path) as $file) {
			/** @var $file \SplFileInfo */
			foreach (ClassInfo::parseClassInfosFromFile($file->getPathname()) as $class_info) {
				/** @var $class ClassInfo */
				yield $class_info;
			}
		}
	}

	/**
	 * @param $path string
	 * @return \Generator
	 */
	protected function getIliasClassFiles($path)
	{
		foreach (
			new \RegexIterator(
				new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($path),
					\RecursiveIteratorIterator::SELF_FIRST,
					\RecursiveIteratorIterator::CATCH_GET_CHILD
				), '/' . self::ILIAS_CLASS_FILE_RE . '/i'
			) as $file
		) {
			yield $file;
		}
	}

	/**
	 * Prints and app usage example
	 */
	protected function printUsage()
	{
		echo sprintf("Usage: %s directory]", __FILE__);
		exit(1);
	}
}

if (isset($_SERVER['argv']) && basename($_SERVER['argv'][0]) == basename(__FILE__)) {
	require_once("./libs/composer/vendor/autoload.php");
	$application = new DicCodeModifier();
	$application->run((array)$_SERVER['argv']);
}
