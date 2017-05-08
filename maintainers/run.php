<?php

error_reporting(E_ALL & ~E_NOTICE);

if (!function_exists('mb_strwidth')) {
	/**
	 * @param $string
	 * @return int
	 */
	function mb_strwidth($string) {
		return strlen($string);
	}
}

use League\CLImate\CLImate;

require_once('vendor/autoload.php');

$cli = new CLImate();
$cli->description("Script for maintenance-info in ILIAS. ");
$cli->arguments->add([
	'path' => [
		'prefix'       => 'p',
		'longPrefix'   => 'path',
		'description'  => 'base Path of the ILIAS-Installation',
		'defaultValue' => '/var/www/ilias',
		'required'     => true,
	],
	'cmd'  => [
		'prefix'      => 'c',
		'longPrefix'  => 'cmd',
		'description' => 'Commands: maintainers, components, generate, usage',
	],
]);

try {
	$cli->arguments->parse();
	$app = new \ILIAS\Tools\Maintainers\App($cli);
	$app->run();
} catch (Exception $e) {
	$cli->shout($e->getMessage());
}


