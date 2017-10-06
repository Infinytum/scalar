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
 * Time: 19:58
 */

namespace Scalar\Log;


use Scalar\IO\Stream\StreamInterface;

class Logger implements LoggerInterface
{

    /**
     * @var StreamInterface
     */
    private $logStream;

    /**
     * Logger constructor.
     * @param $logStream
     */
    public function __construct
    (
        $logStream
    )
    {
        $this->logStream = $logStream;
    }

    /**
     * Write log message to log
     *
     * @param string $logMessage
     * @return void
     */
    public function log
    (
        $logMessage
    )
    {

        if ($this->logStream->isWritable()) {
            $this->logStream->write($logMessage . PHP_EOL);
        }
    }

    public function close()
    {
        $this->logStream->close();
    }

}