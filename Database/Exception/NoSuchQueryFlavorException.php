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
 * Date: 05/09/17
 * Time: 23:13
 */

namespace Scalar\Database\Exception;


class NoSuchQueryFlavorException extends DatabaseException
{

    const ERR_MESSAGE = 'Could not find query flavor "%s" in scalar.';

    const ERR_CODE = 0x3246;

    public function __construct
    (
        $queryFlavor
    )
    {
        parent::__construct
        (
            sprintf(self::ERR_MESSAGE, $queryFlavor),
            self::ERR_CODE
        );
    }

}