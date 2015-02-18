<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Support\Str;
use ReflectionClass;

class DataObjectFactory {

	/**
	 * @param string $dataObjectName
	 * @param array  $parameters
	 * @return mixed
	 * @throws \Exception
	 */
	public static function make($dataObjectName, $parameters = [])
	{
		$class = __NAMESPACE__ . '\\' . ucfirst(Str::camel($dataObjectName));

		if (!class_exists($class)) {
			throw new \Exception('Cannot find DataObject ' . $class);
		}

		$class = new ReflectionClass($class);
		return $class->newInstanceArgs($parameters);
	}

}
