<?php namespace Fideloper\ResourceCache\Http;

use Symfony\Component\HttpFoundation\Request;
use Fideloper\ResourceCache\Resource\ResourceInterface;

class SymfonyRequest implements RequestInterface {

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
    * Determine if resource was modified since the
    * requester's last check
    *
    * For Conditional GET:
    * Uses `If-None-Match` and/or `If-Modified-Since` header.
    * ETag take priority over Last Modified for Validation.
    *
    * @todo  handle `If-None-Match: *`
    * @param Fideloper\ResourceCache\Resource\ResourceInterface
    * @return bool
    */
    public function wasModified(ResourceInterface $resource)
    {
        /*
        * If a request provides one or more
        * ETags, then If-Modified-Since MUST be
        * ignored regardless of presence of a
        * If-Modified-Since header (RFC2616 sec 14.26)
        */
        $usesEtags = false;

        // First, ETag Validation
        $etags = $this->getEtags();

        foreach( $etags as $etag )
        {
            $usesEtags = true;

            // Remove surrounding quotes
            $etag = str_replace('"', '', $etag);

            if( $etag === $resource->getEtag() )
            {
                return false;
            }
        }

        // Second, Modification Date Validation
        $ifModifiedSince = $this->getHeader('if-modified-since');

        if( $ifModifiedSince && $usesEtags === false )
        {
            if( strtotime($ifModifiedSince) >= $resource->getLastUpdated()->getTimestamp() )
            {
                return false;
            }
        }

        return true;
    }

    /**
    * Determine if resource was NOT modified
    * since requester's last check
    *
    * For Concurrency Control (Lost Update Problem)
    * Uses `If-Match` and/or `If-Unmodified-Since`
    */
    public function wasNotModified(ResourceInterface $resource)
    {
        /*
        * Unlike If-None-Match, the spec on If-Match
        * does NOT say if If-Unmodified-Since
        * should be ignored if an ETag was given via
        * If-Match.
        *
        * We'll assume the same behaviour anyway.
        */
        $usesEtags = false;

        // First, ETag Validation
        $etag = str_replace( '"', '', $this->getHeader('if-match') );

        if( $etag )
        {
            $usesEtags = true;

            if($etag !== $resource->getEtag() )
            {
                return false;
            }
        }


        // Second, Modification Date Validation
        $ifUnmodifiedSince = $this->getHeader('if-unmodified-since');

        if( $ifUnmodifiedSince && $usesEtags === false )
        {
            if( strtotime($ifUnmodifiedSince) < $resource->getLastUpdated()->getTimestamp() )
            {
                return false;
            }
        }

        return true;
    }

    /**
    * Get ETags from Request
    *
    * @return array   Array of ETags
    */
    protected function getEtags()
    {
        return $this->request->getETags();
    }

    /**
    * Get specific HTTP header from request
    *
    * @param string   HTTP header to retrieve
    * @return mixed   Result of header
    */
    protected function getHeader($header)
    {
        return $this->request->retrieveItem('headers', $header);
    }

}