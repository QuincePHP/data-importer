<?php namespace Quince\DataImporter\DataObjects;

use ArrayAccess;
use Illuminate\Support\Contracts\ArrayableInterface;

class RowData implements ArrayableInterface, ArrayAccess {

	/**
	 * @var array
	 */
	protected $base = [];

	/**
	 * @var RelationData
	 */
	protected $relation;

	/**
	 * @return array
	 */
	public function getBase()
	{
		return $this->base;
	}

	/**
	 * @param string $header
	 * @param mixed  $value
	 */
	public function setBase($header, $value)
	{
		$this->base[$header] = $value;
	}

	/**
	 * @return RelationData
	 */
	public function getRelation()
	{
		return $this->relation;
	}

	/**
	 * @param RelationData $relation
	 */
	public function setRelation(RelationData $relation)
	{
		$this->relation = $relation;
	}

	/**
	 * @param array $data
	 */
	public function appendToBaseData(Array $data)
	{
		$this->base = array_merge($this->base, $data);
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
			if ($value instanceof RelationData) {
				$tmp[$key] = $value->toArray();
			} else {
				$tmp[$key] = $value;
			}
		}

		return $tmp;
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		if (strtolower($offset) == 'base' || strtolower($offset) == 'relation') {
			return true;
		}

		return false;
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
			return call_user_func([$this, 'get' . camel_case(strtolower($offset)) . 'Array']);
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
		return false;
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		if ($this->offsetExists($offset)) {
			unset($this->$offset);
		}
	}

	/**
	 * @return array
	 */
	public function getBaseArray()
	{
		return $this->base;
	}

	/**
	 * @return array
	 */
	public function getRelationArray()
	{
		return $this->relation->toArray();
	}

}
