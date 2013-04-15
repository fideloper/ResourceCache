<?php

use Mockery as m;
use Fideloper\ResourceCache\Http\SymfonyRequest;

require_once('TestCase.php');

/*
    Use Cases:

    Was Modified:
    1) Uses ETags via if-none-match, finds a match
    2) Uses ETags via if-none-match, finds no match
    3) Uses if-modified-since, and has not been modified since
    4) Uses if-modified-since, and has been modified since


    Was Not Modified
    1) Uses if-match, and does match
    2) Uses if-match, but does not match
    3) Uses if-unmodified-since, and is unmodified since
    4) Uses if-unmodified-since, but is modified since

*/

class SymfonyRequestTest extends TestCase {

    /*
    * Matches an ETag and so never used `If-Modified-Since` logic
    */
    public function testWasModifiedMatchEtag()
    {
        // Mock Request
        $mockRequest = m::mock('Symfony\Component\HttpFoundation\Request');
        $mockRequest->shouldReceive('getETags')->once()->andReturn(array(
            '"'.md5('example1').'"',
            '"'.md5('example2').'"'
        ));

        // Mock Resource
        $mockResource = m::Mock('Fideloper\ResourceCache\Resource\Eloquent\Resource');
        $mockResource->shouldReceive('getEtag')->times(2)->andReturn( md5('example2') );

        $symReq = new SymfonyRequest( $mockRequest );

        $this->assertFalse( $symReq->wasModified($mockResource) );
    }

    /*
    * Does not match an ETag, but still does not use `If-Modified-Since` logic as per spec
    */
    public function testWasModifiedNoMatchEtag()
    {
        // Mock Request
        $now = new DateTime;
        $mockRequest = m::mock('Symfony\Component\HttpFoundation\Request');
        $mockRequest->shouldReceive('retrieveItem')->once()->andReturn( $now->getTimestamp() );
        $mockRequest->shouldReceive('getETags')->once()->andReturn(array(
            '"'.md5('example1').'"',
            '"'.md5('example2').'"'
        ));

        // Mock Resource
        $mockResource = m::Mock('Fideloper\ResourceCache\Resource\Eloquent\Resource');
        $mockResource->shouldReceive('getEtag')->times(2)->andReturn( md5('example3') );

        $symReq = new SymfonyRequest( $mockRequest );

        $this->assertTrue( $symReq->wasModified($mockResource) );
    }

    /*
    * No ETag, attempts Modified Date (was modified)
    */
    public function testWasModifiedIsModified()
    {
        // Mock Request
        $modified_since = '1 March 2013'; // Checking if modified since March 1 (it was - last updated March 3rd)
        $mockRequest = m::mock('Symfony\Component\HttpFoundation\Request');
        $mockRequest->shouldReceive('retrieveItem')->once()->andReturn( $modified_since );
        $mockRequest->shouldReceive('getETags')->once()->andReturn(array());

        // Mock Resource
        $updated_at = strtotime('3 March 2013'); // Resource last updated March 3rd
        $mockResource = m::Mock('Fideloper\ResourceCache\Resource\Eloquent\Resource');
        $mockResource->shouldReceive('getLastUpdated')->times(1)->andReturn( new DateTime('@'.$updated_at) );

        $symReq = new SymfonyRequest( $mockRequest );

        $this->assertTrue( $symReq->wasModified($mockResource), 'Resource was modified since March 1 (modified March 3)' );
    }

    /*
    * No ETag, attempts Modified Date (was not modified)
    */
    public function testWasModifiedIsNotModified()
    {
        // Mock Request
        $modified_since = '3 March 2013'; // Checking if modified since March 3 (it wasn't - last updated March 1st)
        $mockRequest = m::mock('Symfony\Component\HttpFoundation\Request');
        $mockRequest->shouldReceive('retrieveItem')->once()->andReturn( $modified_since );
        $mockRequest->shouldReceive('getETags')->once()->andReturn(array());

        // Mock Resource
        $updated_at = strtotime('1 March 2013'); // Resource last updated March 1st
        $mockResource = m::Mock('Fideloper\ResourceCache\Resource\Eloquent\Resource');
        $mockResource->shouldReceive('getLastUpdated')->times(1)->andReturn( new DateTime('@'.$updated_at) );

        $symReq = new SymfonyRequest( $mockRequest );

        $this->assertFalse( $symReq->wasModified($mockResource), 'Resource was NOT modified since March 3 (modified March 1)' );
    }

    public function testWasNotModified()
    {
        $this->assertTrue( true );
    }

}