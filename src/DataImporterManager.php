<?php namespace Quince\DataImporter;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use League\Csv\Reader;
use Quince\DataImporter\DataObjects\AdditionalFields;
use Quince\DataImporter\DataObjects\FilteredHeaders;
use Quince\DataImporter\DataObjects\RelationData;
use Quince\DataImporter\DataObjects\RelationHeaders;
use Quince\DataImporter\DataObjects\RowData;
use Quince\DataImporter\DataObjects\RowsCollection;
use Quince\DataImporter\DataObjects\TranslatedHeaders;

class DataImporterManager {

	/**
	 * @const string
	 */
	const PACKAGE = 'data-importer';

	/**
	 * @var Container
	 */
	protected $app;

	/**
	 * @var Repository
	 */
	protected $config;

	/**
	 * @var Builder
	 */
	protected $schema;

	/**
	 * @var string
	 */
	protected $filePath;

	/**
	 * @var Reader
	 */
	protected $reader;

	/**
	 * @var FilteredHeaders
	 */
	protected $headers = [];

	/**
	 * @var RelationHeaders
	 */
	protected $relationHeaders = [];

	/**
	 * @var array
	 */
	protected $columnHeaders = [];

	/**
	 * @var bool
	 */
	protected $oneColumn = false;

	/**
	 * @var bool
	 */
	protected $noHeader = false;

	/**
	 * @var int
	 */
	protected $skipRows = 1;

	/**
	 * @param Container  $app
	 * @param Repository $config
	 */
	public function __construct(Container $app, Repository $config)
	{
		$this->app = $app;
		$this->config = $config;
		$this->schema = $this->app->make(Builder::class);
	}

	/**
	 * @param string   $filePath
	 * @param string   $model
	 * @param callable $closure
	 * @param array    $additionalFields
	 * @param array    $aliasDictionary
	 */
	public function import($filePath, $model, callable $closure, $additionalFields = [], $aliasDictionary = [])
	{
		// initialize and storing necessary data
		$this->filePath = $filePath;
		$this->initReader();

		// Get csv file headers and translate it to table column name
		$this->setHeaders($this->app->make($model), $aliasDictionary);

		// loop through csv row and pass data to given closure
		$counter = 0;
		while (true) {
			$data = $this->filterData(
				$this->reader->setOffset($this->getNextOffset($counter), $this->skipRows)->fetchAssoc(),
				new AdditionalFields($additionalFields)
			);

			if ($data->count() == 0) {
				break;
			}

			call_user_func_array($closure, [$data]);
			$counter++;
		}
	}

	/**
	 * Set oneColumn flag to true
	 *
	 * @return $this
	 */
	public function oneColumn()
	{
		$this->oneColumn = true;

		return $this;
	}

	/**
	 * Set noHeader flag to true and skipRows to 0
	 *
	 * @return $this
	 */
	public function noHeader()
	{
		$this->noHeader = false;
		$this->skipRows = 0;

		return $this;
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
	 * @param array $aliasDictionary
	 */
	protected function setHeaders($model, $aliasDictionary)
	{
		$headers = new TranslatedHeaders($this->reader->fetchOne(0), $aliasDictionary);
		$this->headers = $this->filterHeaders($model, $headers);
		$this->relationHeaders = $this->fetchRelationHeader($model, $headers);
	}

	/**
	 * Filter headers that are not in model's table column list
	 *
	 * @param Model             $model
	 * @param TranslatedHeaders $headers
	 * @return FilteredHeaders
	 */
	protected function filterHeaders($model, $headers)
	{
		// get table column list
		$tableColumn = $this->schema->getColumnListing($model->getTable());

		return new FilteredHeaders($headers, $tableColumn);
	}

	/**
	 * @param Model             $model
	 * @param TranslatedHeaders $headers
	 * @return RelationHeaders
	 */
	private function fetchRelationHeader($model, $headers)
	{
		$relationHeaders = new RelationHeaders();

		// get headers which are not in given model columns list
		$otherHeaders = array_diff($headers->toArray(), $this->headers->toArray());

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
	 * @param array            $rawData
	 * @param AdditionalFields $additionalFields
	 * @return RowsCollection
	 */
	protected function filterData($rawData, AdditionalFields $additionalFields)
	{
		$rowsCollection = new RowsCollection();
		$rowData = new RowData();
		$relationData = new RelationData();

		// Loop through rows to filter its data
		foreach ($rawData as $row) {
			// renew data objects
			$rowData->renew();
			$relationData->renew();

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
					foreach ($values as $value) {
						$relationData->addRelationData(
							$this->relationHeaders->getSingleRelation($header)->getRelationName(),
							[$this->relationHeaders->getSingleRelation($header)->getColumnName() => $value]
						);
					}

					// if any additional fields isset for the relation, would append it
					// to relation data object
					if (
					$additionalFields->hasForRelation(
						$this->relationHeaders->getSingleRelation($header)->getRelationName()
					)
					) {
						$relationData->appendToRelationData(
							$this->relationHeaders->getSingleRelation($header)->getRelationName(),
							$additionalFields->getRelationsFields([
									$this->relationHeaders->getSingleRelation($header)->getRelationName()
								])
						);
					}
				} // end of elseif

				// add relations data to row data object
				$rowData->setRelation($relationData);

				if ($additionalFields->hasForBase()) {
					$rowData->appendToBaseData($additionalFields->getBaseFields());
				}
			} // end of row fields foreach

			// Add row object to row collection
			$rowsCollection->addRow($rowData);
		} // end of rows foreach

		return $rowsCollection;
	}

	/**
	 * @param int $iterateCounter
	 * @param int $skiped
	 * @return mixed
	 */
	protected function getNextOffset($iterateCounter, $skiped = 1)
	{
		return $iterateCounter * $this->config->get(self::PACKAGE . '::chunk_size') + $skiped;
	}

}
