<?php namespace Fideloper\ResourceResponse\Resource;

interface ResourceInterface {

    public function getEtag($regen=false);

}