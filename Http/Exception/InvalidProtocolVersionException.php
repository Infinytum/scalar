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
 * Date: 18.05.17
 * Time: 23:24
 */

namespace Scalar\Http\Exception;


use Throwable;

class InvalidProtocolVersionException extends HttpException
{

    private $protocolVersion;

    public function __construct($protocolVersion, $code = 0, Throwable $previous = null)
    {
        $this->protocolVersion = $protocolVersion;
        parent::__construct('Illegal Protocol Version "' . $protocolVersion . '"!', $code, $previous);
    }

    /**
     * Get invalid Protocol Version
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }


}