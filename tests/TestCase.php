<?php

use Mockery as m;

class TestCase extends PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        m::close();
    }

    /**
    * @link   http://stackoverflow.com/questions/4356289/php-random-string-generator
    */
    protected function _randString($length=10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

}