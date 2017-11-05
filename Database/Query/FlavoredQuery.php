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

namespace Scalar\Database\Query;

/**
 * Class FlavoredQuery
 * @package Scalar\Database\Query
 */
class FlavoredQuery
{

    /**
     * PDO query string
     *
     * @var string
     */
    private $queryString;

    /**
     * PDO query placeholder data
     *
     * @var array
     */
    private $queryData;

    /**
     * FlavoredQuery constructor.
     *
     * @param string $queryString
     * @param array $queryData
     */
    public function __construct($queryString, array $queryData)
    {
        $this->queryString = $queryString;
        $this->queryData = $queryData;
    }

    /**
     * Get PDO query with placeholders
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Get PDO placeholder data
     *
     * @return array
     */
    public function getQueryData()
    {
        return $this->queryData;
    }

}