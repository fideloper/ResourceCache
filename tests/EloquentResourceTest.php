<?php

use Mockery as m;
use Fideloper\ResourceCache\Resource\Eloquent\Resource;

require_once('TestCase.php');

class EloquentResourceTest extends TestCase {


    public function testGetEtag()
    {
        $this->_mockConnection();

        $model = new Resource;

        // Of course you do...
        $model->exists = true;

        // Timestamps on in this case
        $model->timestamps = true;

        // Set updated_at to now
        $datetime = new DateTime;
        $model->updated_at = $datetime;

        // Set table name
        $table = $this->_randString();
        $model->setTable($table);

        // Set primary key
        $id = $this->_randString(2);
        $model->setAttribute($model->getKeyName(), $id);

        // Get eTag when not already generated
        $expectedEtag = md5($table . $id . $datetime->format('Y-m-d H:i:s'));
        $this->assertEquals( $expectedEtag , $model->getEtag() );

        // Get eTag when is generated
        $this->assertEquals( $expectedEtag, $model->getEtag() , 'ETag received was the expected ETag' );
    }

    public function testGetLastUpdatedDateTime()
    {
        $model = new Resource;

        // Timestamps on in this case
        $model->timestamps = true;

        // Set updated_at to now
        $datetime = new DateTime;
        $model->updated_at = $datetime;

        $expectedDateTimeString = $datetime->format('Y-m-d H:i:s');

        $this->assertInstanceOf( 'DateTime', $model->getLastUpdated() , 'Returned object is of type DateTime' );
        $this->assertEquals( $expectedDateTimeString , $model->getLastUpdated()->format('Y-m-d H:i:s'), 'Updated At DateTime Object returned' );

        // Now set updated_at to a String representation
        $model->updated_at = "2013-04-15 08:56:16";

        // This is technically a debug on the test itself
        $this->assertInternalType( 'string', $model->updated_at, 'Model updated_at is set to string' );

        $this->assertInstanceOf( 'DateTime', $model->getLastUpdated() , 'Returned object is of type DateTime even when updated_at is a string representation of a datetime' );
    }

    /**
    * Mock some of the internals of DB connection
    * used for proper DateTime format. Assuming MySQL, but
    * any can be used, as long as its DateTIme format is consistent
    */
    protected function _mockConnection()
    {
        Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $resolver->shouldReceive('connection')->andReturn($mockConnection = m::mock('Illuminate\Database\ConnectionInterface'));
        $mockConnection->shouldreceive('getPostProcessor')->andReturn(m::mock('Illuminate\Database\Query\Processors\Processor'));
        $mockConnection->shouldReceive('getQueryGrammar')->andReturn($queryGrammar = m::mock('Illuminate\Database\Query\Grammars\Grammar'));
        $queryGrammar->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
    }


}