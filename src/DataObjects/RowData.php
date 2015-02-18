<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Support\Contracts\ArrayableInterface;

class RowData implements ArrayableInterface {

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

}
