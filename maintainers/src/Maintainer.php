<?php

namespace ILIAS\Tools\Maintainers;

use League\Flysystem\Filesystem;

/**
 * Class Maintainer
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @package ILIAS\Tools\Maintainers
 */
class Maintainer extends JsonSerializable {

	/**
	 * @var \ILIAS\Tools\Maintainers\Maintainer[]
	 */
	protected static $registeredMaintainers = array();
	/**
	 * @var string
	 */
	protected $username = '';
	/**
	 * @var int
	 */
	protected $user_id = 0;


	/**
	 * @return string
	 */
	public function getUsername(): string {
		return $this->username;
	}


	/**
	 * @param string $username
	 */
	public function setUsername(string $username) {
		$this->username = $username;
	}


	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}


	/**
	 * @param int $user_id
	 */
	public function setUserId(int $user_id) {
		$this->user_id = $user_id;
	}


	/**
	 * @return string
	 */
	public function getLinkedProfile(): string {
		switch (true) {
			case ($this->getUserId() == 0 && $this->getUsername() == ''):
				return "MISSING";
			case ($this->getUserId() == 0 && !$this->getUsername() == ''):
				return $this->getUsername();
		}

		return "[{$this->getUsername()}](http://www.ilias.de/docu/goto_docu_usr_{$this->getUserId()}.html)";
	}


	/**
	 * @param $string
	 * @return \ILIAS\Tools\Maintainers\Maintainer
	 */
	public static function fromString($string) {
		if (!$string) {
			return new self();
		}
		$n = new self();

		if (preg_match('/([a-zA-Z0-9]*)\\(([\\d]*)\\)/uUm', $string, $matches)) {
			$username = (string)$matches[1];
			$n->setUsername($username);
			$user_id = (key_exists($username, self::$registeredMaintainers) ? self::$registeredMaintainers[$username]->getUserId() : (int)$matches[2]);
			$n->setUserId($user_id);
		} else {
			$user_id = (key_exists($string, self::$registeredMaintainers) ? self::$registeredMaintainers[$string]->getUserId() : 0);
			$n->setUserId($user_id);
			$n->setUsername($string);
		}
		self::$registeredMaintainers[$n->getUsername()] = $n;

		return $n;
	}


	/**
	 * @param $maintainer
	 * @return string
	 */
	public static function stringify($maintainer) {
		if ($maintainer instanceof Maintainer) {
			return $maintainer->serialize();
		}

		return $maintainer;
	}


	/**
	 * @return string
	 */
	public function serialize() {
		if ($this->getUserId() == 0 && $this->getUsername() == ''
		    || $this->getUsername() == '(0)'
		) {
			return '';
		}
		if (!$this->getUserId() && $this->getUsername()) {
			return $this->getUsername();
		}

		return "{$this->getUsername()}({$this->getUserId()})";
	}


	/**
	 * @param \League\Flysystem\Filesystem $filesystem
	 * @param string $path_to_file
	 */
	public static function writeMaintainerJson(Filesystem $filesystem, $path_to_file = 'Customizing/global/tools/maintainers/maintainers.json') {
		if (!$filesystem->has($path_to_file)) {
			$filesystem->write($path_to_file, '');
		}
		$maintainers = array();
		foreach (self::getRegisteredMaintainers() as $maintainer) {
			$maintainers[$maintainer->getUsername()] = $maintainer->serialize();
		}

		$filesystem->update($path_to_file, JsonSerializable::json_encode($maintainers));
	}


	/**
	 * @param \League\Flysystem\Filesystem $filesystem
	 * @param string $path_to_file
	 */
	public static function loadMaintainerJson(Filesystem $filesystem, $path_to_file = 'Customizing/global/tools/maintainers/maintainers.json') {
		if (!$filesystem->has($path_to_file)) {
			$filesystem->write($path_to_file, '[]');
		}

		foreach (json_decode($filesystem->read($path_to_file)) as $item) {
			$new = self::fromString($item);

			self::$registeredMaintainers[$new->getUsername()] = $new;
		}
	}


	/**
	 * @return \ILIAS\Tools\Maintainers\Maintainer[]
	 */
	public static function getRegisteredMaintainers(): array {
		return self::$registeredMaintainers;
	}


	/**
	 * @param \ILIAS\Tools\Maintainers\Maintainer[] $registeredMaintainers
	 */
	public static function setRegisteredMaintainers(array $registeredMaintainers) {
		self::$registeredMaintainers = $registeredMaintainers;
	}
}