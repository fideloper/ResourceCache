<?php namespace Fideloper\ResourceCache\Http;

use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Fideloper\ResourceCache\Resource\ResourceInterface;

class SymfonyResponse implements ResponseInterface {

    /**
    * Return JSON response from resource
    *
    * @param Fideloper\ResourceCache\Resource\ResourceInterface
    * @param int    HTTP status code
    * @param array  Additional headers
    * @return Symfony\Component\HttpFoundation\JsonResponse
    */
    public function asJson(ResourceInterface $resource, $status = 200, array $headers = array())
    {
        return $this->setCache(
            $resource,
            new JsonResponse($resource->toArray(), $status, $headers)
        );
    }


    /**
    * Return HTML response from resource
    *
    * @param Fideloper\ResourceCache\Resource\ResourceInterface
    * @param string     HTML string output (result of View::make() or similar)
    * @param int    HTTP status code
    * @param array  Additional headers
    * @return Symfony\Component\HttpFoundation\Response
    */
    public function asHtml(ResourceInterface $resource, $output, $status = 200, array $headers = array())
    {
        return $this->setCache(
            $resource,
            new HttpResponse($output, $status, $headers)
        );
    }


    /**
    * Set cache settings (simple for now)
    *
    * @param Fideloper\ResourceCache\Resource\ResourceInterface
    * @param Symfony\Component\HttpFoundation\Response
    * @return Symfony\Component\HttpFoundation\Response
    */
    protected function setCache(ResourceInterface $resource, HttpResponse $response)
    {
        $response->setCache(array(
            'etag' => $resource->getEtag(),
            'last_modified' => $resource->getLastUpdated()
        ));

        return $response;
    }

}