<?php namespace Fideloper\ResourceResponse;

use Illuminate\Support\ServiceProvider;

class ResourceResponseServiceProvider extends ServiceProvider {

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
			$loader->alias('Response', 'Fideloper\ResourceResponse\Facades\Response');

			// Swap Request Facade with own class
			$request = new Http\Request;
			\Request::swap($request->createFromGlobals());
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