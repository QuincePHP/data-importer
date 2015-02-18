<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Support\Contracts\ArrayableInterface;

class HeadersFilter implements ArrayableInterface, \ArrayAccess {

	/**
	 * @var HeadersTranslator
	 */
	protected $headers;

	/**
	 * @var array
	 */
	protected $tableColumn = [];

	/**
	 * @var array
	 */
	protected $filteredHeaders = [];

	/**
	 * @var bool
	 */
	protected $filtered = false;

	/**
	 * @param HeadersTranslator $headers
	 * @param array             $tableColumn
	 */
	public function __construct($headers, $tableColumn)
	{
		$this->headers = $headers;
		$this->tableColumn = $tableColumn;
	}

	/**
	 * @return array
	 */
	public function getFilteredHeaders()
	{
		if ($this->filtered) {
			return $this->filteredHeaders;
		}

		$this->filter();

		return $this->filteredHeaders;
	}

	/**
	 * filter translated headers with table column
	 */
	private function filter()
	{
		$this->filteredHeaders = array_intersect($this->headers->toArray(), $this->tableColumn);

		$this->filtered = true;
	}

	/**
	 * @param string $header
	 * @return bool
	 */
	public function has($header)
	{
		return isset($this->filteredHeaders[$header]);
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->getFilteredHeaders();
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->filteredHeaders[$offset]);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->offsetExists($offset) ? $this->filteredHeaders[$offset] : null;
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
		$this->filteredHeaders[$offset] = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->filteredHeaders[$offset]);
	}

	public function getOtherHeaders()
	{
		return array_diff($this->headers->toArray(), $this->getFilteredHeaders());
	}

}
