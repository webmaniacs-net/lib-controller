<?php


namespace wmlib\controller\Tests;

use wmlib\controller\Request;
use wmlib\controller\Session;
use wmlib\controller\SessionStorage\ArrayMap;
use wmlib\controller\SessionStorage\Native;
use wmlib\controller\Url;


class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    private $request;

    public function setUp()
    {
        parent::setUp();

        $this->request = new Request(new Url('http://example.com/path/path2/path3.ext?x1=y1&x2=y2'), Request::METHOD_GET, ['a1'=>'b1','a2'=>'b2','a3'=>'b3','a4'=>'b4','a5'=>'b5','a6'=>'b6']);
        $this->request = $this->request->withHeader(Request::HEADER_CONTENT_TYPE, 'text/html');
        $this->request = $this->request->withHeader(Request::HEADER_USER_AGENT, 'test user agent');

    }
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
        list($a2, $ax, $a4) = $this->request->getParams('a2', 'aX', 'a4');
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
        $request = $request->withHeader('X-Test2', 'V0')->withAddedHeader('X-Test', 'V1')->withAddedHeader('X-Test', 'V2');
        $headers = $request->getHeaderLines('X-Test');
        $this->assertEquals(['V1', 'V2'], $headers);

        $request = new Request(new Url('http://example.com/path/path2/path3.ext?x1=y1&x2=y2'), Request::METHOD_GET, ['a1' => 'b1']);
        $request = $request->withHeader('X-Test2', 'V0')->withHeader('X-Test', 'V1')->withHeader('X-Test', 'V2');
        $headers = $request->getHeaderLines('X-Test');
        $this->assertEquals(['V2'], $headers);
    }

    public function testSession()
    {
        $sid = microtime();


        $test = $this;

        $session = new Session($sid, new ArrayMap(function($id) use($test, $sid) {
            $test->assertEquals($sid, $id);
            return ['test:k2' => 'v2'];
        }, function($id, array $data) use($test, $sid) {
            $test->assertEquals($sid, $id);
            $test->assertEquals(['test:k1' => 'v1', 'test:k2' => 'v2'], $data);
        }));

        $request = $this->request;
        $this->assertNull($request->getSession());
        $this->assertFalse($request->hasSession());

        $request = $request->withSession($session);

        $this->assertInstanceOf(Session::CLASS, $request->getSession());
        $this->assertFalse($request->hasSession()); // not started yed

        $session->set('test', 'k1', 'v1');
        $this->assertTrue($request->hasSession()); // started after set
    }

    public function testBaseUrl()
    {
        $this->assertEquals('', $this->request->getBaseUrl());

        $subrequest = $this->request->withBaseUrl(new Url('/path/'));

        $this->assertEquals('/path/', $subrequest->getBaseUrl());
    }

    public function testUserAgent()
    {
        $this->assertEquals('test user agent', $this->request->getUserAgent());
    }

    public function testContentType()
    {
        $this->assertEquals('text/html', $this->request->getContentType());
    }

    public function testHasHeader()
    {
        $this->assertTrue($this->request->hasHeader(Request::HEADER_USER_AGENT));
        $this->assertTrue($this->request->hasHeader('user-agent'));
        $this->assertTrue($this->request->hasHeader('uSeR-aGeNt'));
        $this->assertFalse($this->request->hasHeader('x-uSeR-aGeNt'));
    }

    public function testGetHeaders()
    {
        $headers = $this->request->getHeaders();
        $this->assertTrue(is_array($headers));
        foreach($headers as $name => $data) {
            $this->assertTrue(is_array($data));
        }

    }

    public function testGetUri()
    {
        $this->assertInstanceOf(Url::CLASS, $this->request->getUri());
        $this->assertEquals('http://example.com/path/path2/path3.ext?x1=y1&x2=y2', $this->request->getUri());
    }

    public function testWithUri()
    {
        $request = $this->request->withUri(new Url('http://example2.com/path/path12/path13.ext?x1=y1&x2=y2'));

        $this->assertInstanceOf(Url::CLASS, $request->getUri());
        $this->assertEquals('http://example2.com/path/path12/path13.ext?x1=y1&x2=y2', $request->getUri());
    }
}