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

namespace Scalar\Database;

interface DatabaseInterface
{

    public function getName();

    public function getConnectionString();

    public function getUser();

    public function getOptions();

    /**
     * Execute raw query on database
     *
     * @param string $query
     * @return mixed
     */
    public function query
    (
        $query
    );

    /**
     * Get raw pdo instance
     * @return \PDO
     */
    public function getPdoInstance();

    /**
     * Establish connection to database
     *
     * @return bool
     */
    public function connect();

    /**
     * Close connection to database
     *
     * @return void
     */
    public function disconnect();

    /**
     * Returns current database connection status
     *
     * @return bool
     */
    public function isConnected();

}