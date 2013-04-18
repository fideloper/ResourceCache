# Resource Cache

## The Situation
Frameworks don't often give you tools to control HTTP caching, such as setting ETags or Last-Modified dates.

## Goals
This package aims to give you cache control. It's goals are:

1. Allow Validation Caching (using ETags with If-Match,If-None-Match, Last-Modified with If-Modified, If-Unmodified, and so on)
2. Allow Expiration Caching (Using Expires, Last-Modified, Cache-Control and possibly Pragma headers)
3. Help developers learn about HTTP and Caching, a topic which is often ignored

## Installation
[![Build Status](https://travis-ci.org/fideloper/ResourceCache.png?branch=master)](https://travis-ci.org/fideloper/ResourceCache)

This is a Composer package, available on Packagist.

To install it, edit your composer.json file and add:

```json
{
    "require": {
        "fideloper/resourcecache": "dev-master"
    }
}
```

If you are installing this into `Laravel 4`, you then need to add in the Service Provider. To do so, open up `app/config/app.php`, and add this entry with the other Service Providers.

```php
# File: app/config.app.php
'providers' => array(

    ...other providers...

    'Fideloper\ResourceCache\ResourceCacheServiceProvider',

),
```

## Usage
There are two steps:

1. Implementing the Resource interface (This is done for you if you're using Laravel 4)
2. Using the ResourceRequest and ResourceResponse classes with your Resource

### Implement a Resource Interface
This package contains a Request Interface and  Response Interface. These should be implemented to your specific needs.
For example, for Laravel 4, which uses Symfony Request/Response classes, I've created a Symfony implementation for each.

Here is an example implementing Request:

```php
<?php namespace Fideloper\ResourceCache\Http;

use Symfony\Component\HttpFoundation\Request;
use Fideloper\ResourceCache\Resource\ResourceInterface;

class SymfonyRequest implements RequestInterface {

    public function wasModified(ResourceInterface $resource)
    {
        // Do stuff
    }

    public function wasNotModified(ResourceInterface $resource)
    {
        // Do stuff
    }

}
```

Here is an example implementing Response:

```php
# An abbreviated version:
<?php namespace Fideloper\ResourceCache\Http;

use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Fideloper\ResourceCache\Resource\ResourceInterface;

class SymfonyResponse implements ResponseInterface {

    public function asJson(ResourceInterface $resource, $status = 200, array $headers = array())
    {
        // Do things
    }


    public function asHtml(ResourceInterface $resource, $output, $status = 200, array $headers = array())
    {
       // Do things
    }


    protected function setCache(ResourceInterface $resource, HttpResponse $response)
    {
        // Do things
    }

}
```

Lastly, a resource must extend our resource, as it needs to have methods for generating ETags and Lost Modified dates.

Here's an example for Laravel:
```php
<?php namespace Fideloper\ResourceCache\Resource\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Fideloper\ResourceCache\Resource\ResourceInterface;

class Resource extends Model implements ResourceInterface  {

    public function getEtag($regen=false)
    {
        // Do things
    }

    public function getLastUpdated()
    {
        // Do things
    }

}
```

### Using Resource Response/Request with a Resource

The above implementations can be used in your controllers. This ties it all together.
Not pictures is that class `Article` actually extends `Fideloper\ResourceCache\Resource\Eloquent\Resource`, and so isn't explicitly created here with the "new" keyword.

```php
<?php

use Fideloper\ResourceCache\Http\RequestInterface;
use Fideloper\ResourceCache\Http\ResponseInterface;

class SomeController extends BaseController {

    protected $response;
    protected $request;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show($id)
    {
        // This is an instance of Fideloper\ResourceCache\Resource\Eloquent\Resource
        $article = Article::find($id);

        if( ! $this->request->wasModified($article) )
        {
            return Response::json(null, 304);
        }

        return $this->response::asJson($article);
    }

}
```

### If you're using Laravel 4
This is the most terse if you're using Laravel 4, as the Service Provider gives you some Facades to use. If you are a fan in dependency injection, you may wish to skip the use of Facades. In any case, the same `show` method above would appear like this, with Facades:

```php
<?php

# No need for "use" statements with Facades being implemented

class ArticleController extends BaseController {

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show($id)
    {
        $article = Article::find($id);

        if( ! ResourceRequest::wasModified($article) )
        {
            return Response::json(null, 304);
        }

        return ResourceResponse::asJson($article);
    }
}
```

More implementation details will be available in the Wiki.


## Some Explanation
There are a few types of caching:

1. In-app caching (Memcache, Redis, other memory stores)
2. HTTP caching - gateway, proxy and private (aka browsers, and similar)

Making a response (web page, api-response, etc) cachable by third-parties is part of the HTTP cache mechanisms. Which cache mechanisms you use depends on your use case.

The HTTP spec defines 2 methods of HTTP caching:

1. **Validation** - save bandwidth by not having an origin server reply with a full message body (header-only response)
2. **Expiration** - to save round-trips to the origin server - a cache can potentially serve a response directly, saving the origin server from even knowing about the request

Validation caching,  done with if-* headers (if-match, if-modified-since, and so forth) is useful for 2 things (most useful for an API, in my opinion).

1. **Conditional GET requests** - a server can tell the request 'nothing has changed since you last checked'. This is good for mobile APIs where the bandwidth of re-sending a message body can be saved via conditional requests.
2. **Concurrency Control** - in a POST or, more likely, PUT request, a server can check if the resource being updated was changed since the requester last checked (solves the [Lost Update Problem](http://www.w3.org/1999/04/Editing/)). This is good for APIs with a lot of writes (updates) to resources.

Expiration caching, done with Expires, Cache-Control, Last-Modified and other headers, can aid in caching a response for the next user (or even for one specific user), saving your server(s) from some traffic load

1. If you have a gateway cache such as Varnish, you can potentially cache responses to end points per user A gateway cache gives you a lot of cache control since its part of your stack.
2. Setting your responses to being 'public' both in terms of cache control and authentication will allow Proxy caches to cache your site content
3. Requests behind authentication and/or SSL are usually not cached. You may be able to with a gateway cache, or with a private cache (aka, your client can figure out caching based on your expiration headers).
