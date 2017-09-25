<?php
/**
 * (C) 2017 by Michael Teuscher (mk.teuscher@gmail.com)
 * as part of the Scalar PHP framework
 *
 * Released under the AGPL v3.0 license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Scalar\IO;

use PHPUnit\Framework\TestCase;

/**
 * This class tests the functionality of the {@see Scalar\IO\Uri} Scalar\IO\Uri class.
 *
 * @author Cedric Lewe
 * @covers Uri
 */
class UriTest extends TestCase
{
    /**
     * @var Uri
     */
    private static $uri;

    /**
     * @covers Uri::__construct()
     */
    public static function setUpBeforeClass()
    {
        self::$uri = new Uri();
    }

    /**
     * @covers Uri::fromString()
     */
    public function testFromString()
    {
        self::$uri = self::$uri->fromString("Http://user:pass@www.scaly.ch:345/path/to/file?query=param#fragment");
        self::assertNotNull(self::$uri);
    }

    /**
     * @covers  Uri::getHost()
     * @depends testFromString
     */
    public function testGetHost()
    {
        self::assertSame("www.scaly.ch", self::$uri->getHost());
    }

    /**
     * @covers  Uri::getScheme()
     * @depends testFromString
     */
    public function testGetScheme()
    {
        self::assertSame("Http", self::$uri->getScheme());
    }

    /**
     * @covers  Uri::getUserInfo()
     * @depends testFromString
     */
    public function testGetUserInfo()
    {
        self::assertSame("user:pass", self::$uri->getUserInfo());
    }

    /**
     * @covers  Uri::getPort()
     * @depends testFromString
     */
    public function testGetPort()
    {
        self::assertSame(345, self::$uri->getPort());
    }

    /**
     * @covers  Uri::getPath()
     * @depends testFromString
     */
    public function testGetPath()
    {
        self::assertSame("/path/to/file", self::$uri->getPath());
    }

    /**
     * @covers  Uri::getQuery()
     * @depends testFromString
     */
    public function testGetQuery()
    {
        self::assertSame("query=param", self::$uri->getQuery());
    }

    /**
     * @covers  Uri::getQuery()
     * @depends testFromString
     */
    public function testGetFragment()
    {
        self::assertSame("fragment", self::$uri->getFragment());
    }

    /**
     * @covers  Uri::__toString()
     * @depends testFromString
     */
    public function testToString()
    {
        self::assertSame("Http://user:pass@www.scaly.ch:345/path/to/file?query=param#fragment", self::$uri->__toString());
    }

    /**
     * @covers  Uri::withHost()
     * @depends testGetHost
     */
    public function testWithHost()
    {
        $uri = self::$uri->withHost("www.example.com");
        self::assertSame("www.example.com", $uri->getHost());
    }

    /**
     * @covers  Uri::withPort()
     * @depends testGetPort
     */
    public function testWithPort()
    {
        $uri = self::$uri->withPort(675);
        self::assertSame(675, $uri->getPort());
    }

    /**
     * @covers  Uri::withPath()
     * @depends testGetPath
     */
    public function testWithPath()
    {
        $uri = self::$uri->withPath("/foo/bar");
        self::assertSame("/foo/bar", $uri->getPath());
    }

    /**
     * @covers  Uri::withScheme()
     * @depends testGetScheme
     */
    public function testWithScheme()
    {
        $uri = self::$uri->withScheme("https");
        self::assertSame("https", $uri->getScheme());
    }

    /**
     * @covers  Uri::withUserInfo()
     * @depends testGetUserInfo
     */
    public function testWithUserInfo()
    {
        $uri = self::$uri->withUserInfo("chuck", "norris");
        self::assertSame("chuck:norris", $uri->getUserInfo());
    }

    /**
     * @covers  Uri::withQuery()
     * @depends testGetQuery
     */
    public function testWithQuery()
    {
        $uri = self::$uri->withQuery("q");
        self::assertSame("q", $uri->getQuery());
    }

    /**
     * @covers  Uri::withFragment()
     * @depends testGetFragment
     */
    public function testWithFragment()
    {
        $uri = self::$uri->withFragment("q");
        self::assertSame("q", $uri->getFragment());
    }

    /**
     * @covers  Uri::serialize()
     * @depends testFromString
     */
    public function testSerialize()
    {
        self::assertSame("Http://user:pass@www.scaly.ch:345/path/to/file?query=param#fragment", self::$uri->serialize());
    }

    /**
     * @covers  Uri::unserialize()
     * @depends testFromString
     */
    public function testUnserialize()
    {
        $uri = self::$uri;
        self::$uri->unserialize(self::$uri->serialize());
        self::assertSame(self::$uri, $uri);
    }
}