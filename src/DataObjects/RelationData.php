<?php namespace Quince\DataImporter\DataObjects;

use ArrayAccess;
use Illuminate\Support\Contracts\ArrayableInterface;

class RelationData implements ArrayableInterface, ArrayAccess {

	/**
	 * @param $name
	 * @return null
	 */
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
		if (!$this->relationDataIsEmpty($data)) {
			$this->$relation = array_merge((array) $this->$relation, [$data]);
		}
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

	/**
	 * Get an array with the values of a given key.
	 *
	 * @param string $relation
	 * @param string $key
	 * @return array
	 */
	public function lists($relation, $key)
	{
		return array_map(function ($item) use ($key) {
			if (isset($item[$key])) {
				return $item[$key];
			}
		}, (array) $this->$relation);
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->$offset);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		if ($this->offsetExists($offset)) {
			return $this->$offset;
		}

		return null;
	}

	/**
	 * Offset to set
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	protected function relationDataIsEmpty($data)
	{
		return empty(array_filter($data, function ($item) {
			return (!is_null($item) && !empty($item));
		}));
	}
}
