<?php namespace Fideloper\ResourceCache\Http;

use Fideloper\ResourceCache\Resource\ResourceInterface;

interface RequestInterface {

    public function wasModified(ResourceInterface $resource);

    public function wasNotModified(ResourceInterface $resource);

    public function getEtags();

    public function getHeader($header);

}