<?php namespace Fideloper\ResourceResponse\Facades;

use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Response as BaseResponse;
use Fideloper\ResourceResponse\Resource\ResourceInterface;
use Fideloper\ResourceResponse\Format\FormatInterface;

class Response extends BaseResponse {


    /**
    * Return JSON response from resource
    *
    * @param Fideloper\ResourceResponse\Resource\ResourceInterface
    * @param int    HTTP status code
    * @param array  Additional headers
    * @return Symfony\Component\HttpFoundation\JsonResponse
    */
    public static function asJson(ResourceInterface $resource, $status = 200, array $headers = array())
    {
        return self::instance()->setCache(
            $resource,
            new JsonResponse($resource->toArray(), $status, $headers)
        );

    }

    /**
    * Return HTML response from resource
    *
    * @param Fideloper\ResourceResponse\Resource\ResourceInterface
    * @param string     HTML string output (result of View::make() or similar)
    * @param int    HTTP status code
    * @param array  Additional headers
    * @return Symfony\Component\HttpFoundation\Response
    */
    public static function asHtml(ResourceInterface $resource, $output, $status = 200, array $headers = array())
    {
        return self::instance()->setCache(
            $resource,
            new HttpResponse($output, $status, $headers)
        );
    }

    /**
    * Set cache settings (simple for now)
    *
    * @param Fideloper\ResourceResponse\Resource\ResourceInterface
    * @param Symfony\Component\HttpFoundation\Response
    * @return Symfony\Component\HttpFoundation\Response
    */
    protected function setCache(ResourceInterface $resource, HttpResponse $response)
    {
        $response->setCache(array(
            'etag' => $resource->getEtag(),
            'last_modified' => new \DateTime('@'.strtotime($resource->updated_at))
        ));

        return $response;
    }

    public static function instance()
    {
        return new static;
    }
}