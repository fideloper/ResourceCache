<?php

use Mockery as m;
use Fideloper\ResourceCache\Http\SymfonyResponse;

require_once('TestCase.php');

class SymfonyResponseTest extends TestCase {

    public function testAsJson()
    {
        // Mock Resource
        $now = new DateTime;
        $data = array('hasData' => 'yes');
        $mockResource = m::Mock('Fideloper\ResourceCache\Resource\Eloquent\Resource');
        $mockResource->shouldReceive('getEtag')->once()->andReturn( md5('example1') );
        $mockResource->shouldReceive('getLastUpdated')->once()->andReturn( $now );
        $mockResource->shouldReceive('toArray')->once()->andReturn( $data );

        $synResp = new SymfonyResponse;

        $jsonResponse = $synResp->asJson( $mockResource, 201 );

        // General health
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $jsonResponse, 'Response is of type JsonResponse');
        $this->assertEquals( 201, $jsonResponse->getStatusCode(), 'Status code set to what we expected' );
        $this->assertEquals( json_encode($data), $jsonResponse->getContent(), 'Response content is json encoded version of array set' );

        // ETag
        $this->assertEquals( '"'.md5('example1').'"', $jsonResponse->getEtag(), 'ETag set as expected' );

        // Last-Modified
        $this->assertEquals( $now->getTimestamp(), $jsonResponse->getLastModified()->getTimestamp(), 'Last Modified date set as expected' );
    }

    public function testAsHtml()
    {
        // Mock Resource
        $now = new DateTime;
        $content = 'this is the content';
        $mockResource = m::Mock('Fideloper\ResourceCache\Resource\Eloquent\Resource');
        $mockResource->shouldReceive('getEtag')->once()->andReturn( md5('example1') );
        $mockResource->shouldReceive('getLastUpdated')->once()->andReturn( $now );

        $synResp = new SymfonyResponse;

        $htmlResponse = $synResp->asHtml( $mockResource, $content, 200 );

        // General health
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $htmlResponse, 'Response is of type Response');
        $this->assertEquals( 200, $htmlResponse->getStatusCode(), 'Status code set to what we expected' );
        $this->assertEquals( $content, $htmlResponse->getContent(), 'Response content is as set' );

        // ETag
        $this->assertEquals( '"'.md5('example1').'"', $htmlResponse->getEtag(), 'ETag set as expected' );

        // Last-Modified
        $this->assertEquals( $now->getTimestamp(), $htmlResponse->getLastModified()->getTimestamp(), 'Last Modified date set as expected' );
    }

}