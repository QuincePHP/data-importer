<?php namespace Quince\DataImporter;

use Illuminate\Support\ServiceProvider;

class DataImporterServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/config/data-importer.php' => config_path('quince/data-importer.php')
		]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->alias('importer', DataImporterManager::class);

		$this->app->bind('importer', function ($app) {
			return new DataImporterManager($app);
		});

		$this->app->bind('Quince\Contracts\DataImporter\Importer', function ($app) {
			return $app['exporter'];
		});
	}

}
