<?php namespace Fideloper\ResourceCache\Resource;

interface ResourceInterface {

    public function getEtag($regen=false);

}