<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Support\Contracts\ArrayableInterface;

class RelationData implements ArrayableInterface {

	/**
	 * @param string $relation
	 * @param array  $data
	 */
	public function addRelationData($relation, $data)
	{
		$this->$relation[] = $data;
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

}
