<?php namespace Quince\DataImporter\DataObjects;

class Dictionary implements \ArrayAccess {

	protected $terms = [];

	public function getDictionary()
	{
		return $this->terms;
	}

	public function setDictionary($dictionary)
	{
		$this->terms = $dictionary;
	}

	public function getTermTranslation($term)
	{
		return $this->terms[$term];
	}

	public function setTermTranslation($term, $translation)
	{
		$this->terms[$term] = $translation;
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->terms[$offset]);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return isset($this->terms[$offset]) ? $this->terms[$offset] : null;
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
		if (is_null($offset)) {
			throw new \InvalidArgumentException('Invalid terms to set translation for');
		}

		$this->terms[$offset] = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->terms[$offset]);
	}

}
