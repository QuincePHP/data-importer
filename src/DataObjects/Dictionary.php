<?php namespace Quince\DataImporter\DataObjects; 

class Dictionary {

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

}
