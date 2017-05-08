<?php

namespace ILIAS\Tools\Maintainers;

/**
 * Class JsonSerializable
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @package ILIAS\Tools\Maintainers
 */
abstract class JsonSerializable {

	/**
	 * @var bool
	 */
	protected $populated = false;
	/**
	 * @var array
	 */
	protected static $ignored_properties = array( 'populated' );


	/**
	 * @return \stdClass
	 */
	public final function serialize() {
		if ($this->isPopulated()) {
			$this->stringyfy();
			$this->setPopulated(false);
		}
		$i = new \ReflectionClass($this);
		$stdClass = new \stdClass();

		foreach ($i->getProperties() as $property) {
			if ($property->isStatic()
			    || in_array($property->getName(), self::$ignored_properties)
			) {
				continue;
			}
			$property->setAccessible(true);
			$value = $property->getValue($this);
			if ($value instanceof JsonSerializable) {
				$stdClass->{$property->getName()} = $value->serialize();
			} else {
				$stdClass->{$property->getName()} = $value;
			}
		}

		return $stdClass;
	}


	/**
	 * @return string
	 */
	public final function serializeAsJson() {
		return self::json_encode($this->serialize());
	}


	/**
	 * @param \stdClass $serialized
	 */
	public final function unserialize(\stdClass $serialized) {
		$i = new \ReflectionClass($this);
		$default_properties = $i->getDefaultProperties();

		foreach ($i->getProperties() as $property) {
			if ($property->isStatic()
			    || in_array($property->getName(), self::$ignored_properties)
			) {
				continue;
			}
			$property->setAccessible(true);
			$name = $property->getName();
			$var = $serialized->{$name};
			$this->{$name} = is_null($var) ? $default_properties[$name] : $var;
		}
		if (!$this->isPopulated()) {
			$this->populate();
			$this->setPopulated(true);
		}
	}


	/**
	 * @param $json_string
	 */
	public final function unserializeFromJson($json_string) {
		$stdClass = json_decode($json_string);

		return $this->unserialize($stdClass);
	}


	/**
	 * @return string
	 */
	public static function json_encode($data) {
		return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
		                          | JSON_UNESCAPED_SLASHES);
	}


	/**
	 * @return bool
	 */
	private final function isPopulated() {
		return $this->populated;
	}


	/**
	 * @param bool $populated
	 */
	private final function setPopulated($populated) {
		$this->populated = $populated;
	}


	public function populate() {
		if (!$this->isPopulated()) {
			$this->doPopulate();
		}
	}


	public function stringyfy() {
		if ($this->isPopulated()) {
			$this->doStringyfy();
		}
	}


	abstract protected function doPopulate();


	abstract protected function doStringyfy();
}