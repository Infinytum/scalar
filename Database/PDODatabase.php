<?php

namespace Scaly\Database;


class PDODatabase implements DatabaseInterface
{

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
     * @var QueryFlavor
     */
    private $queryFlavor;

    private $helperTableStrings = [];
    private $tableStrings = [];
    private $createdTables = [];


    public function __construct
    (
        $name,
        $connectionString,
        $user = null,
        $pass = null,
        $flavor = 'mysql',
        $pdo = null
    )
    {
        $this->name = $name;
        $this->connectionString = $connectionString;
        $this->user = $user;
        $this->pass = $pass;
        $this->pdo = $pdo;
        $this->scanTables();

        $this->queryFlavor = new QueryFlavor($flavor);
        $this->queryFlavor->load();
    }

    private function scanTables()
    {
        if (!$this->tables) {
            $scanPath = SCALY_APP . '/Database/' . $this->name . '';
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
        $reflectionClass = new \ReflectionClass('Scaly\App\Database\\' . $this->name . '\\' . $class);
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
                    'Query' => $this->queryFlavor->generateCreate($helperTable),
                    'Needs' => []
                ];
            }
        }


        $this->tableStrings[$tableDefinition->getTableName()] = [
            'Table' => $tableDefinition->getTableName(),
            'Query' => $this->queryFlavor->generateCreate($tableDefinition),
            'Needs' => $tableDefinition->getDependencies()
        ];

        return $this->queryFlavor->generateCreate($tableDefinition);
    }

    private function createTable
    (
        $table
    )
    {

        if (in_array($table['Table'], $this->createdTables))
            return;

        /**
         * @var TableDefinition $need
         */
        foreach ($table['Needs'] as $need) {
            $this->createTable($this->tableStrings[$need->getTableName()]);
        }
        $this->pdo->query($table['Query']);
        array_push($this->createdTables, $table['Table']);
    }
}