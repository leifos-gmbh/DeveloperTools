<?php

namespace ILIAS\Tools\Maintainers;

/**
 * Class Directory
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @package ILIAS\Tools\Maintainers
 */
class Directory extends JsonSerializable  {

	const CLASSIC = 'Classic';
	const SERVICE = 'Service';
	/**
	 * @var string
	 */
	protected $maintenance_model = self::CLASSIC;
	/**
	 * @var string
	 */
	protected $first_maintainer = '';
	/**
	 * @var string
	 */
	protected $second_maintainer = '';
	/**
	 * @var array
	 */
	protected $implicit_maintainers = array();
	/**
	 * @var string
	 */
	protected $coordinator = '';
	/**
	 * @var string
	 */
	protected $tester = '';
	/**
	 * @var string
	 */
	protected $testcase_writer = '';
	/**
	 * @var string
	 */
	protected $path = '';
	/**
	 * @var array
	 */
	protected $used_in_components = array();


	/**
	 * Directory constructor.
	 *
	 * @param string $path
	 */
	public function __construct($path) { $this->path = $path; }


	/**
	 * @return bool
	 */
	public function isMaintained() {
		switch ($this->getMaintenanceModel()) {
			case self::CLASSIC:
				return ($this->getFirstMaintainer() != '');
			case self::SERVICE:
				return ($this->getCoordinator() != '');
			default:
				return false;
		}
	}


	/**
	 * @return string
	 */
	public function getMaintenanceModel(): string {
		return $this->maintenance_model;
	}


	/**
	 * @param string $maintenance_model
	 */
	public function setMaintenanceModel(string $maintenance_model) {
		$this->maintenance_model = $maintenance_model;
	}


	/**
	 * @return string
	 */
	public function getFirstMaintainer(): string {
		return $this->first_maintainer;
	}


	/**
	 * @param string $first_maintainer
	 */
	public function setFirstMaintainer(string $first_maintainer) {
		$this->first_maintainer = $first_maintainer;
	}


	/**
	 * @return string
	 */
	public function getSecondMaintainer(): string {
		return $this->second_maintainer;
	}


	/**
	 * @param string $second_maintainer
	 */
	public function setSecondMaintainer(string $second_maintainer) {
		$this->second_maintainer = $second_maintainer;
	}


	/**
	 * @return string
	 */
	public function getTester(): string {
		return $this->tester;
	}


	/**
	 * @param string $tester
	 */
	public function setTester(string $tester) {
		$this->tester = $tester;
	}


	/**
	 * @return string
	 */
	public function getTestcaseWriter(): string {
		return $this->testcase_writer;
	}


	/**
	 * @param string $testcase_writer
	 */
	public function setTestcaseWriter(string $testcase_writer) {
		$this->testcase_writer = $testcase_writer;
	}


	/**
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}


	/**
	 * @param string $path
	 */
	public function setPath(string $path) {
		$this->path = $path;
	}


	/**
	 * @return array
	 */
	public function getUsedinComponents(): array {
		return $this->used_in_components ? $this->used_in_components : array( 'None' );
	}


	/**
	 * @return bool
	 */
	public function hasComponents(): bool {
		return ($this->getUsedinComponents() != array( 'None' ));
	}


	/**
	 * @param array $used_in_components
	 */
	public function setUsedinComponents(array $used_in_components) {
		$this->used_in_components = $used_in_components;
	}


	/**
	 * @return string
	 */
	public function getCoordinator(): string {
		return $this->coordinator;
	}


	/**
	 * @param string $coordinator
	 */
	public function setCoordinator(string $coordinator) {
		$this->coordinator = $coordinator;
	}


	/**
	 * @return array
	 */
	public function getImplicitMaintainers(): array {
		return $this->implicit_maintainers;
	}


	/**
	 * @param array $implicit_maintainers
	 */
	public function setImplicitMaintainers(array $implicit_maintainers) {
		$this->implicit_maintainers = $implicit_maintainers;
	}


	/**
	 * @return \stdClass
	 */
	public function serialize() {
		$this->stringifyMaintainers();

		return parent::serialize();
	}


	/**
	 * @param \stdClass $serialized
	 */
	public function unserialize(\stdClass $serialized) {
		parent::unserialize($serialized);
		$this->populateMaintainers();
	}


	protected function populateMaintainers() {
		$this->first_maintainer = Maintainer::fromString($this->first_maintainer);
		$this->second_maintainer = Maintainer::fromString($this->second_maintainer);
		$this->coordinator = Maintainer::fromString($this->coordinator);
		foreach ($this->implicit_maintainers as $k => $implicit_maintainer) {
			$this->implicit_maintainers[$k] = Maintainer::fromString($implicit_maintainer);
		}
		$this->tester = Maintainer::fromString($this->tester);
		$this->testcase_writer = Maintainer::fromString($this->testcase_writer);
	}


	protected function stringifyMaintainers() {
		$this->first_maintainer = Maintainer::stringify($this->first_maintainer);
		$this->second_maintainer = Maintainer::stringify($this->second_maintainer);
		$this->coordinator = Maintainer::stringify($this->coordinator);
		foreach ($this->implicit_maintainers as $k => $implicit_maintainer) {
			$this->implicit_maintainers[$k] = Maintainer::stringify($implicit_maintainer);
		}
		$this->tester = Maintainer::stringify($this->tester);
		$this->testcase_writer = Maintainer::stringify($this->testcase_writer);
	}
}