<?php
/**
 * Created by PhpStorm.
 * User: Стас
 * Date: 18.01.2015
 * Time: 16:05
 */

namespace wmlib\controller\Tests;

use wmlib\controller\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    public function testUrl()
    {
        $uri = new Url('foo://username:password@example.com:8042/over/%D0%BF%D1%80%D0%BE%D0%B2%D0%B5%D1%80%D0%BA%D0%B0/index.dtb?type=animal&name=narwhal#nose');
        $this->assertEquals('username:password@example.com:8042', $uri->getAuthority());
        $this->assertEquals('/over/проверка/index.dtb', $uri->getPath());
    }

    public function testUrlUnicode()
    {
        $uri = new Url('foo://username:password@example.com:8042/over/%D0%BF%D1%80%D0%BE%D0%B2%D0%B5%D1%80%D0%BA%D0%B0/index.dtb?type=animal&name=narwhal#nose');
        $this->assertEquals('username:password@example.com:8042', $uri->getAuthority());
        $this->assertEquals('/over/проверка/index.dtb', $uri->getPath());
    }


    public function testAbs()
    {
        $uri = new Url('http://user:password@example.com/path/path2?k=v#fragment');
        $this->assertEquals(true, $uri->isAbsolute());

        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('//user:password@example.com/path/path2?k=v', $uri->getSchemeSpecificPart());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('user:password', $uri->getUserInfo());
        $this->assertEquals('/path/path2', $uri->getPath());
        $this->assertEquals('k=v', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }

    public function testRelative()
    {
        $uri = new Url('/path/path2?k=v#fragment');
        $this->assertEquals(false, $uri->isAbsolute());
        $this->assertEquals('/path/path2', $uri->getPath());
        $this->assertEquals('k=v', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }

    public function testGetRelated()
    {
        $uri = new Url('http://user:password@example.com/path/path2?k=v#fragment');
        $related = $uri->getRelated(new Url('/path/'));

        $this->assertEquals('path2?k=v#fragment', (string)$related);
    }

    public function testResolve()
    {
        $base = new Url('http://user:password@example.com/path/path2?k=v#fragment');
        $uri = new Url('/path2?k=v2#fragment2');
        $resolved = $base->resolve($uri);

        $this->assertEquals('http://user:password@example.com/path2?k=v2#fragment2', (string)$resolved);
    }

    public function testResolveRelated()
    {
        $base = new Url('http://user:password@example.com/path/path2?k=v#fragment');
        $uri = new Url('path2?k=v2#fragment2');
        $resolved = $base->resolve($uri);

        $this->assertEquals('http://user:password@example.com/path/path2?k=v2#fragment2', (string)$resolved);
    }

    public function testResolveLeadingDot()
    {
        $base = new Url('http://user:password@example.com/path/path2/?k=v#fragment');
        $uri = new Url('./path2?k=v2#fragment2');
        $resolved = $base->resolve($uri);

        $this->assertEquals('http://user:password@example.com/path/path2/path2?k=v2#fragment2', (string)$resolved);
    }

    public function testResolveFragment()
    {
        $base = new Url('http://user:password@example.com/path/path2?k=v#fragment');
        $uri = new Url('#fragment2');
        $resolved = $base->resolve($uri);

        $this->assertEquals('http://user:password@example.com/path/path2?k=v#fragment2', (string)$resolved);
    }

    public function testAbsString()
    {
        $base = new Url('http://user:password@example.com/path/path2?k=v#fragment');

        $this->assertEquals('http://user:password@example.com/path/path2?k=v#fragment', $base->__toString());
    }

    public function testRelString()
    {
        $base = new Url('/path/path2?k=v#fragment');

        $this->assertEquals('/path/path2?k=v#fragment', $base->__toString());
    }

    public function testNormalize()
    {
        $base = new Url('http://user:password@example.com/./path/../path3/path2?k=v#fragment');
        $base = $base->normalize();

        $this->assertEquals('http://user:password@example.com/path3/path2?k=v#fragment', $base->__toString());
    }
}
