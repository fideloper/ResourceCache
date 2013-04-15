<?php namespace Fideloper\ResourceCache\Http;

use Fideloper\ResourceCache\Resource\ResourceInterface;

interface ResponseInterface {

    public function asJson(ResourceInterface $resource, $status = 200, array $headers = array());

    public function asHtml(ResourceInterface $resource, $output, $status = 200, array $headers = array());
}