<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Contracts\Support\Arrayable;

class FilteredHeaders implements Arrayable {

	/**
	 * @var TranslatedHeaders
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
	 * @param TranslatedHeaders $headers
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

}
