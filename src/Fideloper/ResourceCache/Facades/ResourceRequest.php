<?php namespace Fideloper\ResourceCache\Facades;

use Illuminate\Support\Facades\Facade;

class ResourceRequest extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'resourcerequest'; }

}