<?php namespace Fideloper\ResourceCache;

use Illuminate\Support\ServiceProvider;

class ResourceCacheServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('fideloper/resource-response');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$app = $this->app;

		$app->booting(function() use ($app)
		{
			// Replace Response "facade" (which isn't a Facade)
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('Response', 'Fideloper\ResourceCache\Facades\Response');
		});

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}