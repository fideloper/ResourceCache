<?php namespace Fideloper\ResourceCache\Resource\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Fideloper\ResourceCache\Resource\ResourceInterface;

class Resource extends Model implements ResourceInterface  {

    protected $etag;

    /**
    * Retrieve ETag for single resource
    *
    * @return string ETag for resource
    */
    public function getEtag($regen=false)
    {
        if ( $this->exists && ($this->etag === null || $regen === true)  )
        {
            $this->etag = $this->generateEtag();
        }

        return $this->etag;
    }

    /**
    * Generate ETag for single resource
    *
    * @return string ETag, using md5
    */
    protected function generateEtag()
    {
        $etag = $this->getTable() . $this->getKey();

        if ( $this->usesTimestamps() )
        {
            $datetime = $this->updated_at;

            if ( $datetime instanceof \DateTime )
            {
                $datetime = $this->fromDateTime($datetime);
            }

            $etag .= $datetime;

        }

        return md5( $etag );
    }

}