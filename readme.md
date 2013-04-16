# Resource Cache

## The Situation
Frameworks don't often give you tools to control HTTP caching, such as setting ETags or Last-Modified dates.

## Goals
This package aims to give you cache control. It's goals are:

1. Allow Validation Caching (using ETags with If-Match,If-None-Match, Last-Modified with If-Modified, If-Unmodified, and so on)
2. Allow Expiration Caching (Using Expires, Last-Modified, Cache-Control and possibly Pragma headers)
3. Help developers learn about HTTP and Caching, a topic which is often ignored

## Installation
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