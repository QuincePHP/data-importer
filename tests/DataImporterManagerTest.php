<?php namespace Quince\DataImporter;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Mockery;

class DataImporterManagerTest extends \PHPUnit_Framework_TestCase {

	private $chunkSize = 1000;

	public function setUp()
	{
		parent::setUp();
		Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
	}

	public function testImportingSimpleCsvWithExactHeaders()
	{
		// constant variables
		$model = 'SampleModel';
		$tableName = 'model_table';
		$filePath = __DIR__ . '/data/exact-columns.csv';
		$tableColumn = ['username', 'password', 'email'];

		/**
		 * @var Mockery\MockInterface $app
		 * @var Mockery\MockInterface $config
		 */
		list($app, $config) = $this->mockObjects($model, $tableName, $tableColumn);

		$calledTime = 0;

		$this->getSut($app, $config)->import($filePath, $model, function ($data) use (&$calledTime) {

			/** @var \Quince\DataImporter\DataObjects\RowsCollection $data */
			$this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowsCollection', $data);
			$this->assertLessThanOrEqual($this->getChunkSize(), $data->count());

			/** @var \Quince\DataImporter\DataObjects\RowData $row */
			foreach ($data as $row) {
				$this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowData', $row);
				$this->assertInstanceOf('\Quince\DataImporter\DataObjects\RelationData', $row->getRelation());
				$this->assertTrue($row->getRelation()->isEmpty());
			}

			foreach ($data->toArray() as $value) {
				$this->assertArrayNotHasKey('name', $value['base']);
				$this->assertArrayHasKey('username', $value['base']);
				$this->assertArrayHasKey('email', $value['base']);
				$this->assertArrayHasKey('password', $value['base']);
				$this->assertEmpty($value['relation']);
			}

			$calledTime++;
		});

		$this->assertEquals(ceil(10000 / $this->getChunkSize()), $calledTime);
	}

	public function testImportingSimpleCsvWithExtraHeaders()
	{
		// constant variables
		$model = 'SampleModel';
		$tableName = 'model_table';
		$filePath = __DIR__ . '/data/extra-columns.csv';
		$tableColumn = ['username', 'password', 'email'];

		/**
		 * @var Mockery\MockInterface $app
		 * @var Mockery\MockInterface $config
		 */
		list($app, $config) = $this->mockObjects($model, $tableName, $tableColumn);

		$calledTime = 0;

		$this->getSut($app, $config)->import($filePath, $model, function ($data) use (&$calledTime) {

			/** @var \Quince\DataImporter\DataObjects\RowsCollection $data */
			$this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowsCollection', $data);
			$this->assertLessThanOrEqual($this->getChunkSize(), $data->count());

			/** @var \Quince\DataImporter\DataObjects\RowData $row */
			foreach ($data as $row) {
				$this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowData', $row);
				$this->assertInstanceOf('\Quince\DataImporter\DataObjects\RelationData', $row->getRelation());
				$this->assertTrue($row->getRelation()->isEmpty());
			}

			foreach ($data->toArray() as $value) {
				$this->assertArrayNotHasKey('name', $value['base']);
				$this->assertArrayHasKey('username', $value['base']);
				$this->assertArrayHasKey('email', $value['base']);
				$this->assertArrayHasKey('password', $value['base']);
				$this->assertEmpty($value['relation']);
			}

			$calledTime++;
		});

		$this->assertEquals(ceil(10000 / $this->getChunkSize()), $calledTime);
	}

	public function testImportingCsvWithNoHeadersRow()
	{
		// constant variables
		$model = 'SampleModel';
		$tableName = 'model_table';
		$filePath = __DIR__ . '/data/no-header.csv';
		$tableColumn = ['username', 'password', 'email'];

		$customHeader = ['name', 'username', 'password', 'email'];

		/**
		 * @var Mockery\MockInterface $app
		 * @var Mockery\MockInterface $config
		 */
		list($app, $config) = $this->mockObjects($model, $tableName, $tableColumn);

		$calledTime = 0;

		$this->getSut($app, $config)->noHeadersRow()->setCustomHeaders($customHeader)
		     ->import($filePath, $model, function ($data) use (&$calledTime) {

			     /** @var \Quince\DataImporter\DataObjects\RowsCollection $data */
			     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowsCollection', $data);
			     $this->assertLessThanOrEqual($this->getChunkSize(), $data->count());

			     $itteration = 0;

			     /** @var \Quince\DataImporter\DataObjects\RowData $row */
			     foreach ($data as $row) {
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowData', $row);
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RelationData', $row->getRelation());
				     $this->assertTrue($row->getRelation()->isEmpty());

				     // Check the first row is parsed
				     if ($itteration == 0 && $calledTime == 0) {
					     $this->assertEquals($row->getBase()['username'], 'cpalmer0');
					     $this->assertEquals($row->getBase()['password'], 'qLd2s2T');
					     $this->assertEquals($row->getBase()['email'], 'cpalmer0@biblegateway.com');
				     }

				     $itteration++;
			     }

			     foreach ($data->toArray() as $value) {

				     $this->assertArrayNotHasKey('name', $value['base']);
				     $this->assertArrayHasKey('username', $value['base']);
				     $this->assertArrayHasKey('email', $value['base']);
				     $this->assertArrayHasKey('password', $value['base']);
				     $this->assertEmpty($value['relation']);
			     }

			     $calledTime++;
		     });

		$this->assertEquals(ceil(10001 / $this->getChunkSize()), $calledTime);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Custom header should be specified when headers row sets to false
	 */
	public function testImportingCsvWithNoHeadersRowException()
	{
		// constant variables
		$model = 'SampleModel';
		$tableName = 'model_table';
		$filePath = __DIR__ . '/data/no-header.csv';
		$tableColumn = ['username', 'password', 'email'];

		/**
		 * @var Mockery\MockInterface $app
		 * @var Mockery\MockInterface $config
		 */
		list($app, $config) = $this->mockObjects($model, $tableName, $tableColumn);

		$calledTime = 0;

		$this->getSut($app, $config)->noHeadersRow()
		     ->import($filePath, $model, function () use (&$calledTime) {
			     $calledTime++;
		     });
	}

	public function testSettingHeadersRowOffset()
	{
		// constant variables
		$model = 'SampleModel';
		$tableName = 'model_table';
		$filePath = __DIR__ . '/data/custom-header-rows.csv';
		$tableColumn = ['username', 'password', 'email'];

		/**
		 * @var Mockery\MockInterface $app
		 * @var Mockery\MockInterface $config
		 */
		list($app, $config) = $this->mockObjects($model, $tableName, $tableColumn);

		$calledTime = 0;

		$this->getSut($app, $config)->headersRow(3)
		     ->import($filePath, $model, function ($data) use (&$calledTime) {

			     /** @var \Quince\DataImporter\DataObjects\RowsCollection $data */
			     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowsCollection', $data);
			     $this->assertLessThanOrEqual($this->getChunkSize(), $data->count());

			     /** @var \Quince\DataImporter\DataObjects\RowData $row */
			     foreach ($data as $row) {
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowData', $row);
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RelationData', $row->getRelation());
				     $this->assertTrue($row->getRelation()->isEmpty());
			     }

			     foreach ($data->toArray() as $value) {
				     $this->assertArrayNotHasKey('name', $value['base']);
				     $this->assertArrayHasKey('username', $value['base']);
				     $this->assertArrayHasKey('email', $value['base']);
				     $this->assertArrayHasKey('password', $value['base']);
				     $this->assertEmpty($value['relation']);
			     }

			     $calledTime++;
		     });

		$this->assertEquals(ceil(10001 / $this->getChunkSize()), $calledTime);
	}

	public function testSettingStartRowOffset()
	{
		// constant variables
		$model = 'SampleModel';
		$tableName = 'model_table';
		$filePath = __DIR__ . '/data/exact-columns.csv';
		$tableColumn = ['username', 'password', 'email'];

		/**
		 * @var Mockery\MockInterface $app
		 * @var Mockery\MockInterface $config
		 */
		list($app, $config) = $this->mockObjects($model, $tableName, $tableColumn);

		$calledTime = 0;

		$this->getSut($app, $config)->startFromRow(483)
		     ->import($filePath, $model, function ($data) use (&$calledTime) {

			     /** @var \Quince\DataImporter\DataObjects\RowsCollection $data */
			     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowsCollection', $data);
			     $this->assertLessThanOrEqual($this->getChunkSize(), $data->count());

			     $itteration = 0;

			     /** @var \Quince\DataImporter\DataObjects\RowData $row */
			     foreach ($data as $row) {
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowData', $row);
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RelationData', $row->getRelation());
				     $this->assertTrue($row->getRelation()->isEmpty());

				     // Check the first row is parsed
				     if ($itteration == 0 && $calledTime == 0) {
					     $this->assertEquals($row->getBase()['username'], 'dalexanderde');
					     $this->assertEquals($row->getBase()['password'], 'Wsc3bGyaD');
					     $this->assertEquals($row->getBase()['email'], 'gharperde@yellowbook.com');
				     }

				     $itteration++;
			     }

			     foreach ($data->toArray() as $value) {

				     $this->assertArrayNotHasKey('name', $value['base']);
				     $this->assertArrayHasKey('username', $value['base']);
				     $this->assertArrayHasKey('email', $value['base']);
				     $this->assertArrayHasKey('password', $value['base']);
				     $this->assertEmpty($value['relation']);
			     }

			     $calledTime++;
		     });

		$this->assertEquals(ceil(9518 / $this->getChunkSize()), $calledTime);

	}

	public function testImportingSimpleCsvWithAdditionalFields()
	{
		// constant variables
		$model = 'SampleModel';
		$tableName = 'model_table';
		$filePath = __DIR__ . '/data/exact-columns.csv';
		$tableColumn = ['username', 'password', 'email'];
		$additionalFields = [
			'base' => [
				'usergroup' => '64',
				'active'    => '1'
			]
		];

		/**
		 * @var Mockery\MockInterface $app
		 * @var Mockery\MockInterface $config
		 */
		list($app, $config) = $this->mockObjects($model, $tableName, $tableColumn);

		$calledTime = 0;

		$this->getSut($app, $config)->setAdditionalFields($additionalFields)
		     ->import($filePath, $model, function ($data) use (&$calledTime) {

			     /** @var \Quince\DataImporter\DataObjects\RowsCollection $data */
			     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowsCollection', $data);
			     $this->assertLessThanOrEqual($this->getChunkSize(), $data->count());

			     /** @var \Quince\DataImporter\DataObjects\RowData $row */
			     foreach ($data as $row) {
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowData', $row);
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RelationData', $row->getRelation());
				     $this->assertTrue($row->getRelation()->isEmpty());

				     $this->assertEquals($row->getBase()['usergroup'], '64');
				     $this->assertEquals($row->getBase()['active'], '1');
			     }

			     foreach ($data->toArray() as $value) {
				     $this->assertArrayNotHasKey('name', $value['base']);
				     $this->assertArrayHasKey('username', $value['base']);
				     $this->assertArrayHasKey('email', $value['base']);
				     $this->assertArrayHasKey('password', $value['base']);
				     $this->assertArrayHasKey('usergroup', $value['base']);
				     $this->assertArrayHasKey('active', $value['base']);
				     $this->assertEmpty($value['relation']);
			     }

			     $calledTime++;
		     });

		$this->assertEquals(ceil(10000 / $this->getChunkSize()), $calledTime);
	}

	public function testImportingWithSingleColumnDictionary()
	{
		// constant variables
		$model = 'SampleModel';
		$tableName = 'model_table';
		$filePath = __DIR__ . '/data/not-exact-columns.csv';
		$tableColumn = ['username', 'password', 'email'];

		/**
		 * @var Mockery\MockInterface $app
		 * @var Mockery\MockInterface $config
		 */
		list($app, $config) = $this->mockObjects($model, $tableName, $tableColumn);

		$calledTime = 0;

		$this->getSut($app, $config)->setColumnDictionary('nick name', 'username')
		     ->import($filePath, $model, function ($data) use (&$calledTime) {

			     /** @var \Quince\DataImporter\DataObjects\RowsCollection $data */
			     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowsCollection', $data);
			     $this->assertLessThanOrEqual($this->getChunkSize(), $data->count());

			     /** @var \Quince\DataImporter\DataObjects\RowData $row */
			     foreach ($data as $row) {
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowData', $row);
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RelationData', $row->getRelation());
				     $this->assertTrue($row->getRelation()->isEmpty());
			     }

			     foreach ($data->toArray() as $value) {
				     $this->assertArrayNotHasKey('name', $value['base']);
				     $this->assertArrayHasKey('username', $value['base']);
				     $this->assertArrayHasKey('email', $value['base']);
				     $this->assertArrayHasKey('password', $value['base']);
				     $this->assertEmpty($value['relation']);
			     }

			     $calledTime++;
		     });

		$this->assertEquals(ceil(10000 / $this->getChunkSize()), $calledTime);
	}

	public function testImportingWithDictionary()
	{
		// constant variables
		$model = 'SampleModel';
		$tableName = 'model_table';
		$filePath = __DIR__ . '/data/exact-columns.csv';
		$tableColumn = ['u_name', 'pass', 'email'];

		$dictionary = [
			'username' => 'u_name',
		    'password' => 'pass'
		];

		/**
		 * @var Mockery\MockInterface $app
		 * @var Mockery\MockInterface $config
		 */
		list($app, $config) = $this->mockObjects($model, $tableName, $tableColumn);

		$calledTime = 0;

		$this->getSut($app, $config)->setDictionary($dictionary)
		     ->import($filePath, $model, function ($data) use (&$calledTime) {

			     /** @var \Quince\DataImporter\DataObjects\RowsCollection $data */
			     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowsCollection', $data);
			     $this->assertLessThanOrEqual($this->getChunkSize(), $data->count());

			     /** @var \Quince\DataImporter\DataObjects\RowData $row */
			     foreach ($data as $row) {
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RowData', $row);
				     $this->assertInstanceOf('\Quince\DataImporter\DataObjects\RelationData', $row->getRelation());
				     $this->assertTrue($row->getRelation()->isEmpty());
			     }

			     foreach ($data->toArray() as $value) {
				     $this->assertArrayNotHasKey('name', $value['base']);
				     $this->assertArrayHasKey('u_name', $value['base']);
				     $this->assertArrayHasKey('email', $value['base']);
				     $this->assertArrayHasKey('pass', $value['base']);
				     $this->assertEmpty($value['relation']);
			     }

			     $calledTime++;
		     });

		$this->assertEquals(ceil(10000 / $this->getChunkSize()), $calledTime);
	}

	/**
	 * @param $model
	 * @param $tableName
	 * @param $tableColumn
	 * @return array
	 */
	private function mockObjects($model, $tableName, $tableColumn)
	{
		$app = $this->getMockery(Container::class);
		$config = $this->getMockery(Repository::class);
		$modelInstance = $this->getMockery(Model::class);
		$schema = $this->getMockery(Builder::class);

		$app->shouldReceive('offsetGet')->with('config')->andReturn($config);

		$config->shouldReceive('get')->with(DataImporterManager::PACKAGE . '::delimiter')->andReturn(',');
		$config->shouldReceive('get')->with(DataImporterManager::PACKAGE . '::enclosure')->andReturn('"');
		$config->shouldReceive('get')->with(DataImporterManager::PACKAGE . '::escape')->andReturn('\\');
		$config->shouldReceive('get')->with(DataImporterManager::PACKAGE . '::chunk_size')
		       ->andReturn($this->getChunkSize());
		$config->shouldReceive('get')->with(DataImporterManager::PACKAGE . '::relation_joint')->andReturn('.');

		$app->shouldReceive('make')->once()->with(Builder::class)->andReturn($schema);
		$app->shouldReceive('make')->once()->with($model)->andReturn($modelInstance);
		$modelInstance->shouldReceive('getTable')->once()->andReturn($tableName);
		$schema->shouldReceive('getColumnListing')->once()->with($tableName)->andReturn($tableColumn);
		$app->shouldReceive('make')->once()->with($model)->andReturn($modelInstance);

		return [$app, $config, $modelInstance];
	}

	/**
	 * @param string $mock
	 * @return Mockery\MockInterface
	 */
	private function getMockery($mock)
	{
		return Mockery::mock($mock);
	}

	private function getChunkSize()
	{
		return $this->chunkSize;
	}

	/**
	 * @param Mockery\MockInterface|Container  $app
	 * @param Mockery\MockInterface|Repository $config
	 * @return DataImporterManager
	 */
	protected function getSut($app, $config)
	{
		return new DataImporterManager($app, $config);
	}

}
