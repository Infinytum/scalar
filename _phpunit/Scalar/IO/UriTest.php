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
