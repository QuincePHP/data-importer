<?php namespace Quince\DataImporter\DataObjects;

class AdditionalFields {

	/**
	 * @var array
	 */
	protected $base = [];

	/**
	 * @var array
	 */
	protected $relations = [];

	/**
	 * @param array $additionalFields
	 */
	public function __construct(Array $additionalFields)
	{
		if (isset($additionalFields['base'])) {
			$this->setBase($additionalFields['base']);
		}

		if (isset($additionalFields['relations'])) {
			$this->setRelations($additionalFields['relations']);
		}
	}

	/**
	 * @return array
	 */
	public function getBaseFields()
	{
		return $this->base;
	}

	/**
	 * @param array $base
	 */
	public function setBase(Array $base)
	{
		$this->base = $base;
	}

	/**
	 * @param null|string $relation
	 * @return array
	 */
	public function getRelationsFields($relation = null)
	{
		if (!is_null($relation)) {
			return $this->relations[$relation];
		}

		return $this->relations;
	}

	/**
	 * @param array $relations
	 */
	public function setRelations(Array $relations)
	{
		foreach ($relations as $relation => $additionalFields) {
			$this->relations[$relation] = (array) $additionalFields;
		}
	}

	/**
	 * @param string $relation
	 * @return bool
	 */
	public function hasForRelation($relation)
	{
		return (isset($this->relations[$relation]) && is_array($this->relations[$relation]));
	}

	/**
	 * @return bool
	 */
	public function hasForBase()
	{
		return (!empty($this->base) && is_array($this->base));
	}

}
