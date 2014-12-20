<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Support\Contracts\ArrayableInterface;

class SingleRelationHeaderMap implements ArrayableInterface {

	/**
	 * @var string
	 */
	protected $relation;

	/**
	 * @var string
	 */
	protected $column;

	/**
	 * @param string $relation
	 * @param string $column
	 */
	function __construct($relation, $column)
	{
		$this->relation = $relation;
		$this->column = $column;
	}

	/**
	 * @return string
	 */
	public function getRelationName()
	{
		return $this->relation;
	}

	/**
	 * @return string
	 */
	public function getColumnName()
	{
		return $this->column;
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
