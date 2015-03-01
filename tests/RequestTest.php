<?php


namespace wmlib\controller\Tests;

use wmlib\controller\Request;
use wmlib\controller\Url;


class RequestTest extends \PHPUnit_Framework_TestCase
{

    public function testProtocol()
    {
        $request = new Request(new Url('http://example.com'), Request::METHOD_GET, []);

        $this->assertEquals('1.1', $request->getProtocolVersion());

        $request = new Request( new Url('http://example.com'), Request::METHOD_GET, [], 1);

        $this->assertEquals('1.0', $request->getProtocolVersion());

        $request = new Request( new Url('http://example.com'), Request::METHOD_GET, [], 0.911);

        $this->assertEquals('0.9', $request->getProtocolVersion());

        $request = new Request( new Url('http://example.com'), Request::METHOD_GET, [], 1.1);

        $this->assertEquals('1.1', $request->getProtocolVersion());

        $request = new Request( new Url('http://example.com'), Request::METHOD_GET, [], 2);

        $this->assertEquals('2.0', $request->getProtocolVersion());
    }

    public function testWithBaseUrl()
    {
        $request = new Request(new Url('http://example.com/path/path2/path3.ext'), Request::METHOD_GET, []);
        $subrequest = $request->withBaseUrl(new Url('/path/'));

        $this->assertEquals('path2/path3.ext', $subrequest->getUrlPath());
    }

    public function testGetParams()
    {
        $request = new Request(new Url('http://example.com/path/path2/path3.ext'), Request::METHOD_GET, ['a1'=>'b1','a2'=>'b2','a3'=>'b3','a4'=>'b4','a5'=>'b5','a6'=>'b6']);

        list($a2, $ax, $a4) = $request->getParams('a2', 'aX', 'a4');
        $this->assertEquals('b2', $a2);
        $this->assertEquals(null, $ax);
        $this->assertEquals('b4', $a4);
    }

    public function testGetQueryParams()
    {
        $request = new Request(new Url('http://example.com/path/path2/path3.ext?x1=y1&x2=y2'), Request::METHOD_GET, ['a1' => 'b1']);

        $params = $request->getQueryParams();
        $this->assertEquals(['x1' => 'y1', 'x2' => 'y2'], $params);
    }

    public function testGetHeaderLines()
    {
        $request = new Request(new Url('http://example.com/path/path2/path3.ext?x1=y1&x2=y2'), Request::METHOD_GET, ['a1' => 'b1']);
        $request = $request->withAddedHeader('X-Test', 'V1')->withAddedHeader('X-Test', 'V2');
        $headers = $request->getHeaderLines('X-Test');
        $this->assertEquals(['V1', 'V2'], $headers);

        $request = new Request(new Url('http://example.com/path/path2/path3.ext?x1=y1&x2=y2'), Request::METHOD_GET, ['a1' => 'b1']);
        $request = $request->withHeader('X-Test', 'V1')->withHeader('X-Test', 'V2');
        $headers = $request->getHeaderLines('X-Test');
        $this->assertEquals(['V2'], $headers);
    }
}