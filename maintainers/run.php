<?php

// namespace ILIAS\Tools\Maintainers;
error_reporting(E_ALL);

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
	],
	'cmd'  => [
		'prefix'       => 'c',
		'longPrefix'   => 'cmd',
		'description'  => 'Commands: maintainers, components, generate, usage',
	],
]);
$cli->arguments->parse();
$app = new \ILIAS\Tools\Maintainers\App($cli);
$app->run();


