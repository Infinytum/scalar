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

namespace Scalar\Exception;


/**
 * Class ScalarException
 *
 * Core Exception Class
 *
 * @package Scalar\Exception
 */
class ScalarException extends \Exception
{

    const ERR_GENERIC_TEMPLATE = '%s Help may be available at https://help.scaly.ch/error/0x%x';

    /**
     * Error message from Scalar
     *
     * @var string $message
     */
    protected $message;

    /**
     * Unique error code
     *
     * @var $code
     */
    protected $code;

    /**
     * Original exception that was thrown
     *
     * @var \Throwable $innerException
     */
    protected $innerException;

    /**
     * Addition data provided by thrower
     *
     * @var array $additionalData
     */
    protected $additionalData;

    public function __construct
    (
        $message,
        $code,
        $innerException = null,
        $additionalData = []
    )
    {
        $this->message = $message;
        $this->code = $code;
        $this->innerException = $innerException;
        $this->additionalData = $additionalData;
        parent::__construct
        (
            sprintf(self::ERR_GENERIC_TEMPLATE, $message, $code),
            $code,
            $innerException
        );
    }

}