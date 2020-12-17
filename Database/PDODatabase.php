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


use Scalar\Database\Query\Flavor;
use Scalar\Database\Table\FieldDefinition;
use Scalar\Database\Table\TableDefinition;
use Scalar\Util\ScalarArray;

class PDODatabase implements DatabaseInterface
{

    /**
     * PDO Connection pool
     *
     * @var ScalarArray
     */
    private static $databaseConnections;

    /**
     * @var string
     */
    private $connectionString;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $pass;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $tables = [];

    /**
     * @var Flavor
     */
    private $flavor;

    private $helperTableStrings = [];
    private $tableStrings = [];
    private $createdTables = [];
    private $beingCreated = [];


    public function __construct
    (
        $name,
        $connectionString,
        $user = null,
        $pass = null,
        $flavor = Flavor::LANG_MYSQL,
        $pdo = null
    )
    {
        $this->name = $name;
        $this->connectionString = $connectionString;
        $this->user = $user;
        $this->pass = $pass;
        $this->pdo = $pdo;
        $this->scanTables();

        $this->flavor = Flavor::byName($flavor);

        if (self::$databaseConnections === null) {
            self::$databaseConnections = new ScalarArray();
        }
    }

    private function scanTables()
    {
        if (!$this->tables) {
            $scanPath = SCALAR_APP . '/Database/' . $this->name . '';
            $result = glob($scanPath . '/*.php');
            $classes = [];

            foreach ($result as $item) {
                array_push($classes, pathinfo(basename($item), PATHINFO_FILENAME));
            }
            $this->tables = $classes;
        }

        return $this->tables;
    }

    /**
     * Execute raw query on database
     *
     * @param string $query
     * @return mixed
     */
    public function query
    (
        $query
    )
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->pdo->query($query);
    }

    /**
     * Returns current database connection status
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->pdo != null;
    }

    /**
     * Execute query with placeholders on database
     *
     * @param string $query
     * @param array $param
     * @return mixed
     */
    public function execute
    (
        $query,
        $param
    )
    {
        foreach ($param as $key => $value) {
            if ($value === true) {
                $value = 1;
            } else if ($value === false) {
                $value = 0;
            }
            $param[$key] = $value;
        }
        $statement = $this->pdo->prepare($query);
        $result = $statement->execute($param);

        if ($result) {
            $row = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $row;
        }

        return $result;
    }

    /**
     * Get raw pdo instance
     * @return \PDO
     */
    public function getPdoInstance()
    {
        return $this->pdo;
    }

    /**
     * Establish connection to database
     *
     * @return bool
     * @throws \PDOException In case connection fails
     */
    public function connect()
    {
        if ($this->isConnected()) {
            return true;
        }

        $this->pdo = new \PDO
        (
            $this->connectionString,
            $this->user,
            $this->pass,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_BOTH,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        self::$databaseConnections->set($this->name, $this);

        return true;
    }

    /**
     * Close connection to database
     *
     * @return void
     */
    public function disconnect()
    {
        $this->pdo = null;
        unset(self::$databaseConnections[$this->name]);
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getConnectionString()
    {
        return $this->connectionString;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getOptions()
    {
        return
            [
                "Pass" => $this->pass
            ];
    }

    public function deploy()
    {
        foreach ($this->tables as $table) {

            $tableDefinition = $this->fetchTableDefinition($table);

            if (!$tableDefinition) {
                continue;
            }

            $this->generateCreateQuery($tableDefinition);
        }

        foreach ($this->tableStrings as $table => $tableData) {
            $this->createTable($tableData);
        }

        foreach ($this->helperTableStrings as $table => $tableData) {
            $this->createTable($tableData);
        }
    }

    /**
     * @param $class
     * @return TableDefinition|null
     */
    private function fetchTableDefinition($class)
    {
        $reflectionClass = new \ReflectionClass('Scalar\App\Database\\' . $this->name . '\\' . $class);
        return $reflectionClass->getMethod('getTableDefinition')->invoke(null);
    }

    /**
     * @param TableDefinition $tableDefinition
     * @return mixed
     */
    private function generateCreateQuery
    (
        $tableDefinition
    )
    {
        /**
         * @var FieldDefinition $fieldDefinition
         */
        foreach ($tableDefinition->getMultiRelations() as $fieldDefinition) {
            $foreignTable = $fieldDefinition->getForeignTableDefinition();

            if (!$foreignTable)
                continue;

            $helperTable = $fieldDefinition->getHelperTableDefinition();
            if (!array_key_exists($helperTable->getTableName(), $this->tableStrings)) {
                $this->helperTableStrings[$helperTable->getTableName()] = [
                    'Table' => $helperTable->getTableName(),
                    'Query' => $this->flavor->generateCreate($helperTable),
                    'Needs' => []
                ];
            }
        }


        $this->tableStrings[$tableDefinition->getTableName()] = [
            'Table' => $tableDefinition->getTableName(),
            'Query' => $this->flavor->generateCreate($tableDefinition),
            'Needs' => $tableDefinition->getDependencies()
        ];

        return $this->flavor->generateCreate($tableDefinition);
    }

    private function createTable
    (
        $table
    )
    {

        if (in_array($table['Table'], $this->createdTables))
            return;

        array_push($this->beingCreated, $table['Table']);

        /**
         * @var TableDefinition $need
         */
        foreach ($table['Needs'] as $needIndex => $need) {
            if (in_array($need->getTableName(), $this->beingCreated))
                continue;
            $this->createTable($this->tableStrings[$need->getTableName()]);
        }

        array_pop($this->beingCreated);

        $this->pdo->query($table['Query']);
        array_push($this->createdTables, $table['Table']);
    }

    /**
     * Get database connection
     *
     * @param string $databaseName
     * @return PDODatabase|null
     */
    public static function getPDO
    (
        $databaseName
    )
    {
        return self::$databaseConnections->getPath($databaseName);
    }
}