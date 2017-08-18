<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 8/18/17
 * Time: 1:27 PM
 */

namespace Scalar\IO;

use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{

    public function testUriFromString()
    {
        $uri = new Uri();
        /**
         * @var $uri Uri
         */
        $uri = $uri->fromString("Http://user:pass@www.scaly.ch:345/path/to/file?query=param#fragment");
        self::assertEquals("www.scaly.ch", $uri->getHost());
        self::assertEquals("Http", $uri->getScheme());
        self::assertEquals("user:pass", $uri->getUserInfo());
        self::assertEquals(345, $uri->getPort());
        self::assertEquals("/path/to/file", $uri->getPath());
        self::assertEquals("query=param", $uri->getQuery());
        self::assertEquals("fragment", $uri->getFragment());
        self::assertEquals("Http://user:pass@www.scaly.ch:345/path/to/file?query=param#fragment", $uri);

        /**
         * @var $uri Uri
         */
        $uri = $uri->fromString("Http://www.scaly.ch/path/to/file?query=param");
        self::assertEquals("www.scaly.ch", $uri->getHost());
        self::assertEquals("Http", $uri->getScheme());
        self::assertEquals("", $uri->getUserInfo());
        self::assertEquals(null, $uri->getPort());
        self::assertEquals("/path/to/file", $uri->getPath());
        self::assertEquals("query=param", $uri->getQuery());
        self::assertEquals("", $uri->getFragment());
        self::assertEquals("Http://www.scaly.ch/path/to/file?query=param", $uri->__toString());

        $newUri = $uri->withScheme("sftp");
        self::assertNotEquals("sftp", $uri->getScheme());
        self::assertEquals("sftp", $newUri->getScheme());

        $serializedUri = serialize($uri);
        self::assertEquals($uri, unserialize($serializedUri));
    }

}
