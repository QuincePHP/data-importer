<?php namespace Quince\DataImporter\DataObjects;

use Illuminate\Contracts\Support\Arrayable;

class TranslatedHeaders implements Arrayable {

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

	public function getTranslatedHeader()
	{
		if ($this->translated) {
			return $this->translatedHeader;
		}

		$this->translate();

		return $this->translatedHeader;
	}

	protected function translate()
	{
		foreach ($this->headers as $header) {
			$this->translatedHeader[$header] = $this->getTranslation($header);
		}

		$this->translated = true;
	}

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
		return $this->getTranslatedHeader();
	}

}
