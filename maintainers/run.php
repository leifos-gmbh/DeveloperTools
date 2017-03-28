<?php

// namespace ILIAS\Tools\Maintainers;
error_reporting(E_ALL);

use League\CLImate\CLImate;

require_once('vendor/autoload.php');
$cli = new CLImate();
$cli->arguments->add([
	'path' => [
		'prefix'       => 'p',
		'longPrefix'   => 'path',
		'description'  => 'base Path of the ILIAS-Installation',
		'defaultValue' => '/var/www/ilias',
	],
]);
$cli->usage();
$cli->arguments->parse();
//$cli->draw('bender');
$MaintenanceReader = new ILIAS\Tools\Maintainers\Iterator($cli->arguments->get('path'));
$MaintenanceReader->runFor(array( 'Services', 'Modules', 'src' ));
$cli->shout('ILIAS has ' . $MaintenanceReader->getCollector()->howManyMaintained() . ' maintained and '
            . $MaintenanceReader->getCollector()->howManyUnmaintained() . ' unmaintained Directories in '
            . $MaintenanceReader->getCollector()->howManyComponents() . ' components');
$cli->out("Available Components:\n\n" . $MaintenanceReader->getCollector()->getAvailableComponentsAsString());
$cli->out("Writing MD-File");




