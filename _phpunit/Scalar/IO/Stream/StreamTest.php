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
 * Time: 1:40 PM
 */

namespace Scalar\IO\Stream;

use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    /**
     * @var Stream
     */
    private static $streamReadWrite;

    /**
     * @var StreamInterface
     */
    private static $streamWrite;

    /**
     * @var StreamInterface
     */
    private static $streamRead;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$streamReadWrite = new Stream(fopen("php://temp", "w+"));
        self::$streamRead = new Stream(fopen("php://temp", "r"));
        self::$streamWrite = new Stream(fopen("php://temp", "w"));
    }

    public function testIsWritable()
    {
        self::assertTrue(self::$streamReadWrite->isWritable());
        self::assertFalse(self::$streamRead->isWritable());
        self::assertTrue(self::$streamWrite->isWritable());
    }

    public function testIsReadable()
    {
        self::assertTrue(self::$streamReadWrite->isReadable());
        self::assertTrue(self::$streamRead->isReadable());
        self::assertTrue(self::$streamWrite->isReadable());
    }

    public function testWipe()
    {
        self::$streamReadWrite->write("Test");
        self::$streamReadWrite->rewind();
        self::assertEquals(self::$streamReadWrite->getContents(), "Test");
        self::$streamReadWrite->wipe();
        self::$streamReadWrite->rewind();
        self::assertEquals(self::$streamReadWrite->getContents(), "");
    }

}
