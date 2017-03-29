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
	 * @return \stdClass
	 */
	public function serialize() {
		$i = new \ReflectionClass($this);
		$stdClass = new \stdClass();

		foreach ($i->getProperties() as $property) {
			if ($property->isStatic()) {
				continue;
			}
			$property->setAccessible(true);
			$value = $property->getValue($this);
			if($value instanceof JsonSerializable) {
				$stdClass->{$property->getName()} = $value->serialize();
			}else {
				$stdClass->{$property->getName()} = $value;
			}
		}

		return $stdClass;
	}


	/**
	 * @return string
	 */
	public function serializeAsJson() {
		return self::json_encode($this->serialize());
	}


	/**
	 * @param \stdClass $serialized
	 */
	public function unserialize(\stdClass $serialized) {
		$i = new \ReflectionClass($this);
		$default_properties = $i->getDefaultProperties();

		foreach ($i->getProperties() as $property) {
			if ($property->isStatic()) {
				continue;
			}
			$property->setAccessible(true);
			$name = $property->getName();
			$var = $serialized->{$name};
			$this->{$name} = is_null($var) ? $default_properties[$name] : $var;
		}
	}


	/**
	 * @param $json_string
	 */
	public function unserializeFromJson($json_string) {
		$stdClass = json_decode($json_string);

		return $this->unserialize($stdClass);
	}


	/**
	 * @return string
	 */
	public static function json_encode($data): string {
		return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
		                          | JSON_UNESCAPED_SLASHES);
	}
}