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

namespace Scalar\Cache\Exception;

class InvalidKeyException extends \Exception implements InvalidArgumentException
{

    const INVALID_KEY_EXCEPTION = 'Invalid Key "%s" passed to Cache Layer';

    public function __construct($message = "", $arguments, $code = 0, $throwable = null)
    {
        array_unshift($arguments, $message);
        $message = call_user_func_array("sprintf", $arguments);
        parent::__construct($message, $code, $throwable);
    }

}