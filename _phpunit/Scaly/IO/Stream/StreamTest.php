<?php
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
