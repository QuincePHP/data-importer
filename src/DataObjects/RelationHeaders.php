<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Contracts\Support\Arrayable;

class RelationHeaders implements Arrayable {

	/**
	 * @var array|SingleRelationHeaderMap[]
	 */
	protected $container = [];

	/**
	 * @param string $header
	 * @param string $relation
	 * @param string $column
	 */
	public function addRelationHeader($header, $relation, $column)
	{
		$this->container[$header] = new SingleRelationHeaderMap($relation, $column);
	}

	/**
	 * @return array
	 */
	public function getRelationHeaders()
	{
		return $this->container;
	}

	/**
	 * @param string $header
	 * @return SingleRelationHeaderMap
	 */
	public function getSingleRelation($header)
	{
		return $this->container[$header];
	}

	/**
	 * @param string $header
	 * @return bool
	 */
	public function has($header)
	{
		return isset($this->container[$header]);
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		$tmp = [];

		foreach ($this->container as $key => $value) {
			if ($value instanceof SingleRelationHeaderMap) {
				$value = $value->toArray();
			}

			$tmp[$key] = $value;
		}
		
		return $tmp;
	}

}
