<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Support\Contracts\ArrayableInterface;

class HeadersTranslator implements ArrayableInterface {

	/**
	 * @var array
	 */
	protected $headers = [];

	/**
	 * @var array
	 */
	protected $dictionary = [];

	/**
	 * @var array
	 */
	protected $translatedHeader = [];

	/**
	 * @var bool
	 */
	protected $translated = false;

	/**
	 * @param array $headers
	 * @param array $dictionary
	 */
	public function __construct($headers, $dictionary)
	{
		$this->headers = $headers;
		$this->dictionary = $dictionary;
	}

	/**
	 * Get translated headers array
	 *
	 * @return array
	 */
	public function getTranslatedHeaders()
	{
		if ($this->translated) {
			return $this->translatedHeader;
		}

		$this->translate();

		return $this->translatedHeader;
	}

	/**
	 * Generate headers translation
	 */
	protected function translate()
	{
		foreach ($this->headers as $header) {
			$this->translatedHeader[$header] = $this->getTranslation($header);
		}

		$this->translated = true;
	}

	/**
	 * Get translation of specified header
	 *
	 * @param $header
	 * @return mixed
	 */
	protected function getTranslation($header)
	{
		if (isset($this->dictionary[$header])) {
			return $this->dictionary[$header];
		} else {
			return $header;
		}
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->getTranslatedHeaders();
	}

}
