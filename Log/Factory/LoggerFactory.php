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
 * Date: 09.06.17
 * Time: 21:34
 */

namespace Scalar\Log\Factory;


use Scalar\IO\Factory\StreamFactory;
use Scalar\IO\Stream\StreamInterface;
use Scalar\Log\Logger;
use Scalar\Log\LoggerInterface;

class LoggerFactory implements LoggerFactoryInterface
{

    /**
     * Create logger with stream
     *
     * @param StreamInterface $streamInterface
     * @return LoggerInterface
     */
    public function createLogger
    (
        $streamInterface
    )
    {
        return new Logger($streamInterface);
    }

    /**
     * Create logger with file
     *
     * @param string $filePath
     * @return LoggerInterface
     */
    public function createLoggerFromFile
    (
        $filePath
    )
    {
        $streamFactory = new StreamFactory();
        return new Logger($streamFactory->createStreamFromFile($filePath, 'r+'));
    }
}