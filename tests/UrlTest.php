<?php


namespace wmlib\controller\Tests;


use wmlib\controller\Url;


class UrlTest extends \PHPUnit_Framework_TestCase
{

    public function testWithQueryValue()
    {
        $url = new Url('http://example.com/path/path2/path3.ext?x1=y1&x2=y2');
        $url = $url->withQueryValue('x3', 'v3');

        $this->assertEquals('http://example.com/path/path2/path3.ext?x1=y1&x2=y2&x3=v3', $url);
        $this->assertInstanceOf(Url::CLASS, $url);
    }


    public function testWithQueryValues()
    {
        $url = new Url('http://example.com/path/path2/path3.ext?x1=y1&x2=y2');
        $url = $url->withQueryValues(['x2' => 'y21','x3' => 'v3', 'x4' => 'v4']);

        $this->assertEquals('http://example.com/path/path2/path3.ext?x1=y1&x2=y21&x3=v3&x4=v4', $url);
        $this->assertInstanceOf(Url::CLASS, $url);
    }
}