<?php


namespace wmlib\controller\Tests;

use wmlib\controller\Request;
use wmlib\controller\Url;


class RequestTest extends \PHPUnit_Framework_TestCase {

    public function testProtocol()
    {
        $request = new Request(null, new Url('http://example.com'), Request::METHOD_GET, []);

        $this->assertEquals('1.1', $request->getProtocolVersion());

        $request = new Request(null, new Url('http://example.com'), Request::METHOD_GET, [], 1);

        $this->assertEquals('1.0', $request->getProtocolVersion());

        $request = new Request(null, new Url('http://example.com'), Request::METHOD_GET, [], 0.911);

        $this->assertEquals('0.9', $request->getProtocolVersion());

        $request = new Request(null, new Url('http://example.com'), Request::METHOD_GET, [], 1.1);

        $this->assertEquals('1.1', $request->getProtocolVersion());

        $request = new Request(null, new Url('http://example.com'), Request::METHOD_GET, [], 2);

        $this->assertEquals('2.0', $request->getProtocolVersion());
    }
}