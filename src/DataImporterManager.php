<?php namespace Quince\DataImporter;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use League\Csv\Reader;
use Quince\DataImporter\DataObjects\DataObjectFactory;

class DataImporterManager {

	/**
	 * Package alias name
	 *
	 * @const string
	 */
	const PACKAGE = 'data-importer';

	/**
	 * Application Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Config repository
	 *
	 * @var Repository
	 */
	protected $config;

	/**
	 * Weather file has header row or not
	 *
	 * @var bool
	 */
	protected $headersRow = true;

	/**
	 * Row offset of header row
	 *
	 * @var int
	 */
	protected $headersRowOffset = 0;

	/**
	 * Offset of row to start
	 *
	 * @var int
	 */
	protected $startRow = 1;

	/**
	 * Columns to be used
	 *
	 * @var array
	 */
	protected $desiredHeaders = [];

	/**
	 * Additional fields to be added
	 *
	 * @var DataObjects\AdditionalFields
	 */
	protected $additionalFields;

	/**
	 * Headers dictionary
	 *
	 * @var DataObjects\Dictionary
	 */
	protected $dictionary;

	/**
	 * Path to file to be imported
	 *
	 * @var string
	 */
	protected $filePath;

	/**
	 * Reader instance
	 *
	 * @var Reader
	 */
	protected $reader;

	/**
	 * Headers to be used
	 *
	 * @var DataObjects\HeadersFilter
	 */
	protected $headers;

	/**
	 * Headers of relation columns
	 *
	 * @var DataObjects\RelationHeaders
	 */
	protected $relationHeaders;

	/**
	 * Instantiate data importer manager
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
		$this->config = $app['config'];
	}

	/**
	 * Set headers row existence to false
	 *
	 * @return DataImporterManager
	 */
	public function noHeadersRow()
	{
		$this->headersRow = false;
		$this->startFromRow(0);

		return $this;
	}

	/**
	 * Determine read heders from which row
	 *
	 * @param int $rowOffset
	 * @return DataImporterManager
	 */
	public function headersRow($rowOffset)
	{
		$this->headersRowOffset = $rowOffset;

		return $this;
	}

	/**
	 * Determine from which line to start
	 *
	 * @param int $rowOffset
	 * @return DataImporterManager
	 */
	public function startFromRow($rowOffset)
	{
		$this->startRow = $rowOffset;

		return $this;
	}

	/**
	 * Set desired headers when no header row exist
	 *
	 * @param array $headers
	 * @return DataImporterManager
	 */
	public function setCustomHeaders($headers)
	{
		$this->desiredHeaders = $headers;

		return $this;
	}

	/**
	 * Determine fields that are not in file and should be set in results
	 *
	 * @param array $fields
	 * @return DataImporterManager
	 */
	public function setAdditionalFields($fields)
	{
		$this->additionalFields = DataObjectFactory::make('AdditionalFields', [$fields]);

		return $this;
	}

	/**
	 * Set a column translation, file header to sql column name
	 *
	 * @param string $columnName
	 * @param string $translation
	 * @return DataImporterManager
	 */
	public function setColumnDictionary($columnName, $translation)
	{
		$this->getDictionaryObject()->setTermTranslation($columnName, $translation);

		return $this;
	}

	/**
	 * Set headers translation, file headers to sql columns name
	 *
	 * @param array $dictionary
	 * @return DataImporterManager
	 */
	public function setDictionary($dictionary)
	{
		$this->getDictionaryObject()->setDictionary($dictionary);

		return $this;
	}

	/**
	 * Import data from given file to given model
	 *
	 * @param string   $filePath
	 * @param string   $model
	 * @param callable $closure
	 */
	public function import($filePath, $model, callable $closure)
	{
		// initialize and storing necessary data
		$this->filePath = $filePath;
		$this->initReader();

		// Get csv file headers and translate it to table column name
		$this->setHeaders($this->app->make($model));

		// loop through csv row and pass data to given closure
		$counter = 0;
		while (true) {
			$data = $this->filterData(
				$this->reader->setOffset($this->getNextOffset($counter))
				             ->setLimit($this->config->get(self::PACKAGE . '::chunk_size'))
				             ->fetchAssoc()
			);

			if ($data->count() == 0) {
				break;
			}

			call_user_func_array($closure, [$data]);
			$counter++;
		}
	}

	/**
	 * Initialize and configuer reader class
	 */
	protected function initReader()
	{
		// Instansiate reader with given file path
		$this->reader = Reader::createFromPath($this->filePath);

		// Config the reader
		$this->reader->setDelimiter($this->config->get(self::PACKAGE . '::delimiter'));
		$this->reader->setEnclosure($this->config->get(self::PACKAGE . '::enclosure'));
		$this->reader->setEscape($this->config->get(self::PACKAGE . '::escape'));

		// Set limit size foreach chunk of rows
		$this->reader->setLimit($this->config->get(self::PACKAGE . '::chunk_size'));
	}

	/**
	 * Set import headers
	 *
	 * @param Model $model
	 */
	protected function setHeaders($model)
	{
		$headers = DataObjectFactory::make('HeadersTranslator', [
			$this->getHeaders(), $this->getDictionaryObject()
		]);

		$this->headers = $this->filterHeaders($model, $headers);
		$this->relationHeaders = $this->fetchRelationHeader($model, $headers);
	}

	/**
	 * Get datasheet columns headers
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function getHeaders()
	{
		if (!$this->headersRow) {
			if (!empty($this->desiredHeaders)) {
				return $this->desiredHeaders;
			} else {
				throw new \Exception();
			}
		}

		return $this->reader->fetchOne($this->headersRowOffset);
	}

	/**
	 * Filter headers that are not in model's table column list
	 *
	 * @param Model                         $model
	 * @param DataObjects\HeadersTranslator $headers
	 * @return DataObjects\HeadersFilter
	 */
	protected function filterHeaders($model, $headers)
	{
		// get table column list
		$tableColumn = $this->getSchemeBuilder()->getColumnListing($model->getTable());

		return DataObjectFactory::make('HeadersFilter', [$headers, $tableColumn]);
	}

	/**
	 * Get schema builder instance
	 *
	 * @return Builder
	 */
	protected function getSchemeBuilder()
	{
		return $this->app->make(Builder::class);
	}

	/**
	 * Get relation headers
	 *
	 * @param Model $model
	 * @return DataObjects\RelationHeaders
	 */
	private function fetchRelationHeader($model)
	{
		/** @var DataObjects\RelationHeaders $relationHeaders */
		$relationHeaders = DataObjectFactory::make('RelationHeaders');

		// get headers which are not in given model columns list
		$otherHeaders = $this->headers->getOtherHeaders();

		// loop through other headers to find any relation headers
		foreach ($otherHeaders as $key => $value) {
			// check if header has relation joint character
			if (strpos($value, $this->config->get(self::PACKAGE . '::relation_joint'))) {
				list($relation, $column) = explode($this->config->get(self::PACKAGE . '::relation_joint'), $value);

				if ($model->touches($relation)) {
					$relationHeaders->addRelationHeader($key, $relation, $column);
				}
			}
		}

		return $relationHeaders;
	}

	/**
	 * Get next offset
	 *
	 * @param int $iterateCounter
	 * @return mixed
	 */
	protected function getNextOffset($iterateCounter)
	{
		return $iterateCounter * $this->config->get(self::PACKAGE . '::chunk_size') + $this->startRow;
	}

	/**
	 * Filter data with desired headers
	 *
	 * @param array $rawData
	 * @return DataObjects\RowsCollection
	 */
	protected function filterData($rawData)
	{
		$rowsCollection = DataObjectFactory::make('RowsCollection');

		// Loop through rows to filter its data
		foreach ($rawData as $row) {
			/**
			 * @var DataObjects\RowData      $rowData
			 * @var DataObjects\RelationData $relationData
			 */
			$rowData = DataObjectFactory::make('RowData');
			$relationData = DataObjectFactory::make('RelationData');

			// Loop through each field in a row
			foreach ($row as $header => $fieldValue) {

				// check if field header is set in importable headers list
				// if it's not there check if it's in relation header list
				if ($this->headers->has($header)) {
					$rowData->setBase($this->headers[$header], $fieldValue);
				} elseif ($this->relationHeaders->has($header)) {
					// Explode relation field value if have many values
					$values = explode(
						$this->config->get(self::PACKAGE . '::relation_value_delimiter'),
						$fieldValue
					);

					// Set data foreach relation value
					$singleRelationHeaderMap = $this->relationHeaders->getSingleRelation($header);
					foreach ($values as $value) {
						$relationData->addRelationData(
							$singleRelationHeaderMap->getRelationName(),
							[$singleRelationHeaderMap->getColumnName() => $value]
						);
					}

					// if any additional fields isset for the relation, would append it
					// to relation data object
					if (
						!is_null($this->additionalFields) &&
						$this->additionalFields->hasForRelation($singleRelationHeaderMap->getRelationName())
					) {
						$relationData->appendToRelationData(
							$singleRelationHeaderMap->getRelationName(),
							$this->additionalFields->getRelationsFields([
								$singleRelationHeaderMap->getRelationName()
							])
						);
					}
				} // end of elseif

				// add relations data to row data object
				$rowData->setRelation($relationData);

				if (!is_null($this->additionalFields) && $this->additionalFields->hasForBase()) {
					$rowData->appendToBaseData($this->additionalFields->getBaseFields());
				}
			} // end of row fields foreach

			// Add row object to row collection
			$rowsCollection->addRow($rowData);

		} // end of rows foreach

		return $rowsCollection;
	}

	/**
	 * Get Dictionary data object
	 *
	 * @return DataObjects\Dictionary
	 */
	protected function getDictionaryObject()
	{
		if (!isset($this->dictionary)) {
			$this->dictionary = DataObjectFactory::make('Dictionary');
		}

		return $this->dictionary;
	}

}
