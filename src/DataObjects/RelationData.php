<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Support\Contracts\ArrayableInterface;

class RelationData implements ArrayableInterface {

	function __get($name)
	{
		if (isset($this->$name)) {
			return $this->$name;
		}

		return null;
	}

	/**
	 * @param string $relation
	 * @param array  $data
	 */
	public function addRelationData($relation, $data)
	{
		$this->$relation = array_merge((array) $this->$relation, [$data]);
	}

	/**
	 * @param string $relation
	 * @param array  $data
	 */
	public function appendToRelationData($relation, Array $data)
	{
		foreach ($this->$relation as &$relationDate) {
			$relationDate = array_merge($relationDate, $data);
		}
	}

	/**
	 * @param string $relation
	 * @return array|null
	 */
	public function getRelationData($relation)
	{
		if (isset($this->$relation)) {
			return $this->$relation;
		}

		return null;
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		$tmp = [];

		foreach ($this as $key => $value) {
			$tmp[$key] = $value;
		}

		return $tmp;
	}

	/**
	 * Check if data is
	 *
	 * @return bool
	 */
	public function isEmpty()
	{
		foreach ($this as $prop) {
			return false;
		}

		return true;
	}

}
