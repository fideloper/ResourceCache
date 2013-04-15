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
		// Bind Resource objects to App IoC
		$this->app['resourcerequest'] = $this->app->share(function($app)
		{
			return new Http\SymfonyRequest( $app['request'] );
		});

		$this->app['resourceresponse'] = $this->app->share(function($app)
		{
			return new Http\SymfonyResponse;
		});

		// Load Facade Aliases
		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();

			$loader->alias('ResourceRequest', 'Fideloper\ResourceCache\Facades\ResourceRequest');
			$loader->alias('ResourceResponse', 'Fideloper\ResourceCache\Facades\ResourceResponse');
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