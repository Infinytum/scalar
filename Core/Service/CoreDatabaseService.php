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

namespace Scalar\Core\Service;


use Scalar\Config\IniConfig;
use Scalar\Core\Scalar;
use Scalar\Database\Exception\NoSuchDatabaseException;
use Scalar\Database\PDODatabase;
use Scalar\IO\Exception\IOException;
use Scalar\IO\File;

class CoreDatabaseService extends CoreService
{

    // Configuration

    const CONFIG_DATABASE_LIST = 'List';

    // Variables

    /**
     * CoreLogger instance
     * @var CoreLoggerService
     */
    private $coreLogger;

    /**
     * List of all databases with configuration
     * @var IniConfig
     */
    private $databaseList;

    public function __construct()
    {
        $this->coreLogger = Scalar::getService(Scalar::SERVICE_CORE_LOGGER);
        parent::__construct('Database');
    }

    /**
     * @param string $databaseName
     * @return PDODatabase
     * @throws NoSuchDatabaseException Thrown if database is not configured
     */
    public function getDatabase
    (
        $databaseName
    )
    {
        if (!$this->hasDatabase($databaseName)) {
            throw new NoSuchDatabaseException($databaseName);
        }

        return new PDODatabase
        (
            $databaseName,
            $this->databaseList->getPath($databaseName . '.ConnectionString'),
            $this->databaseList->getPath($databaseName . '.User'),
            $this->databaseList->getPath($databaseName . '.Pass')
        );
    }

    /**
     * Check if a database is configured
     *
     * @param string $databaseName
     * @return bool
     */
    public function hasDatabase
    (
        $databaseName
    )
    {
        return $this->databaseList->has($databaseName);
    }

    /**
     * Initialize service for work
     *
     * @return bool
     */
    public function setup()
    {
        try {
            $this->addDefault(self::CONFIG_DATABASE_LIST, '{{App.Home}}/database.list');
        } catch (IOException $ex) {
            $this->coreLogger->e('An error occurred while saving the database configuration list: ' . $ex);
        }

        $databaseListFile = new File($this->getValue(self::CONFIG_DATABASE_LIST));


        if ((!$databaseListFile->exists() && !$databaseListFile->canCreate()) || (!$databaseListFile->isWritable() && $databaseListFile->exists())) {
            $this->coreLogger->e('Cannot create database configuration! Fail-over to in-memory configuration');
            $databaseListFile = fopen('php://temp', 'r+');
        }

        $this->databaseList = new IniConfig($databaseListFile, [], true);
        $this->databaseList->load();

        $this->databaseList
            ->setDefaultPath('MyDatabase.ConnectionString', 'mysql:host=localhost:3306;dbname=myDatabase;charset=utf8')
            ->setDefaultPath('MyDatabase.User', 'root')
            ->setDefaultPath('MyDatabase.Pass', 'password');

        return true;
    }

    /**
     * Destruct service and execute tearDown tasks
     *
     * @return bool
     */
    public function tearDown()
    {
        try {
            $this->databaseList->save();
        } catch (IOException $ex) {
            $this->coreLogger->e('An error occurred while saving the database configuration list: ' . $ex);
        }

        return true;
    }
}