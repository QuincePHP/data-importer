<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Contracts\Support\Arrayable;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

class RowsCollection implements Arrayable, IteratorAggregate {

	/**
	 * @var array|RowData[]
	 */
	protected $rows = [];

	/**
	 * @return RowData
	 */
	public function getRows()
	{
		return $this->rows;
	}

	/**
	 * @param RowData $row
	 */
	public function addRow(RowData $row)
	{
		array_push($this->rows, $row);
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->rows);
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		$tmp = [];

		foreach ($this->rows as $row) {
			$tmp[] = $row->toArray();
		}

		return $tmp;
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @return Traversable
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->rows);
	}

}
