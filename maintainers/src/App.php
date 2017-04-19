<?php

namespace ILIAS\Tools\Maintainers;

use League\CLImate\CLImate;

/**
 * Class App
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @package ILIAS\Tools\Maintainers
 */
class App {

	const CMD_GENERATE = 'generate';
	const CMD_COMPONENTS = 'components';
	const CMD_MAINTAINERS = 'maintainers';
	/**
	 * @var \League\CLImate\CLImate
	 */
	protected $cli;


	/**
	 * App constructor.
	 *
	 * @param \League\CLImate\CLImate $cli
	 */
	public function __construct(CLImate $cli) { $this->cli = $cli; }


	public function run() {
		if (!is_dir($this->cli->arguments->get('path'))) {
			throw new \Exception("ILIAS base path does not exists. Please specify by using the -p option: php run.php -p /path/to/ilias/root -c command");
		}

		switch ($this->cli->arguments->get('cmd')) {
			default:
				$this->cli->usage();
				break;
			case self::CMD_COMPONENTS:
				$MaintenanceReader = new Iterator($this->cli->arguments->get('path'));
				$MaintenanceReader->loadFiles();

				$this->cli->out("Available Components:\n\n");

				$c = array();
				foreach (Component::getRegistredInstances() as $component) {
					$c[] = array(
						'name'  => $component->getName(),
						'model' => $component->getModel(),
					);
				}

				$this->cli->table($c);

				break;

			case self::CMD_MAINTAINERS:
				$MaintenanceReader = new Iterator($this->cli->arguments->get('path'));
				$MaintenanceReader->loadFiles();

				$this->cli->out("Available Maintainers:\n\n");

				$c = array();
				foreach (Maintainer::getRegisteredMaintainers() as $maintainer) {
					$c[] = array(
						'username' => $maintainer->getUsername(),
						'user-id'    => $maintainer->getUserId(),
					);
				}

				$this->cli->table($c);

				break;

			case self::CMD_GENERATE:
				$MaintenanceReader = new Iterator($this->cli->arguments->get('path'));
				$MaintenanceReader->runFor(array( 'Services', 'Modules', 'src' ));
				$this->cli->shout('ILIAS has ' . $MaintenanceReader->getCollector()
				                                                   ->howManyMaintained()
				                  . ' maintained and ' . $MaintenanceReader->getCollector()
				                                                           ->howManyUnmaintained()
				                  . ' unmaintained Directories in '
				                  . $MaintenanceReader->getCollector()->howManyComponents()
				                  . ' components');

				$this->cli->out("Writing MD-File");
				break;
		}
	}
}