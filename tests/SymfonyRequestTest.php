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

    public function testWasModified()
    {
        $httpReq = $this->_mockRequest();

        $symReq = new SymfonyRequest( $httpReq );

        $this->assertTrue( true );
    }

    public function testWasNotModified()
    {
        $this->assertTrue( true );
    }

    protected function _mockRequest()
    {
        $mock = m::mock('Symfony\Component\HttpFoundation\Request');

        $now = new DateTime();

        $mock->shouldReceive('getHeader')->once()->andReturn( $now->format('Y-m-d H:i:s') );

        $mock->shouldReceive('getEtags')->once()->andReturn(array(
            '"'.md5('example1').'"',
            '"'.md5('example2').'"'
        ));
    }

    protected function _mockResource()
    {
        return m::mock('Fideloper\ResourceCache\Resource\ResourceInterface');
    }

}