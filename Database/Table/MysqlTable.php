<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 12.06.17
 * Time: 16:01
 */

namespace Scaly\Database\Table;

use Scaly\Config\JsonConfig;
use Scaly\Database\PDODatabase;
use Scaly\Database\QueryFlavor;
use Scaly\Util\Annotation\PHPDoc;
use Scaly\Util\Factory\AnnotationFactory;
use Scaly\Util\FilterableInterface;
use Scaly\Util\ScalyArray;

abstract class MysqlTable implements FilterableInterface
{

    /**
     * @var PDODatabase
     */
    private static $database;
    /**
     * @var static
     */
    private $mockObject;
    /**
     * @var \ReflectionProperty[]
     */
    private $fields;
    /**
     * @var ScalyArray
     */
    private $query;
    /**
     * @var QueryFlavor
     */
    private $queryFlavor;
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var array
     */
    private $updateOverrides;

    public function __construct
    (
        $tableName,
        $updateFieldOverrides = []
    )
    {
        $this->tableName = $tableName;
        $this->updateOverrides = $updateFieldOverrides;
        $this->resetQuery();
        $this->queryFlavor = new QueryFlavor('mysql');
        $this->queryFlavor->load();
    }

    public function resetQuery()
    {
        $this->query = new ScalyArray
        (
            [
                'Table' => $this->tableName,
                'Selector' => '*'
            ]
        );
    }

    /**
     * Set global PDO instance
     *
     * @param PDODatabase $pdo
     */
    public static function setPDO
    (
        $pdo
    )
    {
        self::$database = $pdo;
    }

    public abstract static function fromRow
    (
        $row
    );

    /**
     * Filter data on SQL Server
     * @param $lambda callable filter
     * @return self
     */
    public function whereNot($lambda)
    {
        $mockObject = $this->generateMockInstance();
        $mock = &$mockObject;
        $field = $lambda($mock);

        $this->query->putPath('Where.NotEqual',
            $field
        );
        return $this;
    }

    /**
     * @return \stdClass
     */
    private function generateMockInstance()
    {
        if ($this->mockObject) {
            return clone $this->mockObject;
        }
        $mock = new \stdClass();
        foreach ($this->getFields()->asArray() as $property) {
            $mock->{$property->name} = $property->name;
        }
        $this->mockObject = $mock;
        return $mock;
    }

    /**
     * Get all SQL fields
     */
    private function getFields()
    {
        if ($this->fields) {
            return $this->fields;
        }
        $reflectionClass = new \ReflectionClass(get_called_class());
        $properties = new ScalyArray($reflectionClass->getProperties());

        $fields = $properties->where
        (
            function (
                $key,
                $value
            ) {
                $annotationFactory = new AnnotationFactory();
                $annotations = new ScalyArray
                (
                    $annotationFactory->createAnnotationArrayFromString
                    (
                        $value->getDocComment()
                    )
                );

                return $annotations->where
                (
                    function ($index, $annotation) {
                        return $annotation->getName() == 'Field';
                    }
                )->any();
            }
        );

        $this->fields = $fields;
        return $fields;
    }

    /**
     * Filter data from all objects in array
     * @param $lambda callable filter
     */
    public function whereLike($lambda)
    {
        $mockObject = $this->generateMockInstance();
        $mock = &$mockObject;
        $field = $lambda($mock);

        $this->query->putPath('Where.Like',
            $field
        );
        return $this;
    }

    /**
     * Filter data from all objects in array
     * @param $lambda callable filter
     */
    public function whereNotLike($lambda)
    {
        $mockObject = $this->generateMockInstance();
        $mock = &$mockObject;
        $field = $lambda($mock);

        $this->query->putPath('Where.NotLike',
            [
                $field
            ]
        );
        return $this;
    }

    /**
     * Get first object or default value
     * @param $default mixed
     * @return mixed
     */
    public function firstOrDefault($default = null)
    {
        // TODO: Implement firstOrDefault() method.
    }

    /**
     * Get last object or default value
     * @param $default mixed
     * @return mixed
     */
    public function lastOrDefault($default = null)
    {
        // TODO: Implement lastOrDefault() method.
    }

    /**
     * Check if data contains any entries
     * @return bool
     */
    public function any()
    {
        $data = $this->fetch();

        if ($data === null) {
            return false;
        }

        if (is_array($data)) {
            return $this->count($data) > 0;
        }

        return true;
    }

    /**
     * Fetch data
     * @return mixed
     */
    public function fetch()
    {
        $tableDefinition = self::getTableDefinition();
        $query = $this->getSelectQuery();
        $rows = self::getPDO()->execute($query[0], $query[1]);

        if (!$rows) {
            return null;
        }


        $data = [];

        $reflectionClass = new \ReflectionClass(get_called_class());
        $method = $reflectionClass->getMethod('fromRow');


        foreach ($rows as $row) {

            foreach ($tableDefinition->getMultiRelations() as $fieldDefinition) {
                $this->resetQuery();
                $helperTable = $fieldDefinition->getHelperTableDefinition();
                $this->query->setPath('Table', $helperTable->getTableName());
                $this->select
                (
                    function ($mock) use ($fieldDefinition) {
                        return $fieldDefinition
                            ->getForeignTableDefinition()
                            ->getField($fieldDefinition->getForeignHelperColumn())
                            ->getHelperColumnName();
                    }

                );

                $this->where
                (
                    function ($mock) use ($fieldDefinition, $tableDefinition) {
                        return $tableDefinition->getField($fieldDefinition->getLocalHelperColumn())->getHelperColumnName();
                    },
                    $row[$fieldDefinition->getLocalHelperColumn()]
                );;
                $query = $this->getSelectQuery();
                $result = self::getPDO()->execute($query[0], $query[1]);

                $ids = [];
                $fieldName = $fieldDefinition->getForeignTableDefinition()->getTableName();
                $fieldName .= '_';
                $fieldName .= $fieldDefinition->getForeignHelperColumn();
                foreach ($result as $re) {
                    array_push($ids, $re[$fieldName]);
                }

                $row[$fieldDefinition->getFieldName()] = $ids;
            }
            array_push($data, $method->invoke(null, $row));

        }

        if (count($data) == 1) {
            return $data[0];
        }
        return $data;
    }

    /**
     * Get generated table definition
     *
     * @return null|TableDefinition
     */
    public static function getTableDefinition()
    {
        $reflectionClass = new \ReflectionClass(get_called_class());
        $definitionPath = dirname($reflectionClass->getFileName()) . '/' . $reflectionClass->getShortName() . '.json';

        $definitionLoader = new JsonConfig($definitionPath, []);
        $definitionLoader->load();

        if (!$definitionLoader->has('Table')) {
            $reflectionFields = $reflectionClass->getProperties();

            $definitionLoader->set('Table', self::getParameters($reflectionClass));

            if (!$definitionLoader->hasPath('Table.Table')) {
                return null;
            }

            foreach ($reflectionFields as $reflectionField) {
                $params = self::getParameters($reflectionField);
                if (array_key_exists('Field', $params)) {
                    $definitionLoader->setPath('Fields.' . $reflectionField->name, $params);
                }

            }
            $definitionLoader->save();
        }

        return new TableDefinition($definitionLoader->asScalyArray());
    }

    private static function getParameters
    (
        $reflectionClass
    )
    {
        $phpDoc = new PHPDoc($reflectionClass);
        return $phpDoc->getAnnotations();
    }

    public function getSelectQuery()
    {
        return $this->queryFlavor->generateSelect(
            $this->query->asArray()
        );
    }

    /**
     * Get global PDO instance
     *
     * @return PDODatabase
     */
    public static function getPDO()
    {
        return self::$database;
    }

    /**
     * Retrieve sub-data from all objects in array
     * @param $lambda callable filter
     * @return self
     */
    public function select($lambda)
    {
        $mockObject = $this->generateMockInstance();
        $mock = &$mockObject;
        $field = $lambda($mock);

        if (is_array($field)) {
            $field = join(', ', $field);
        }
        $this->query->setPath('Selector', $field);
    }

    /**
     * Filter data on SQL Server
     * @param $lambda callable filter
     * @return self
     */
    public function where($lambda)
    {
        $mockObject = $this->generateMockInstance();
        $mock = &$mockObject;
        $field = $lambda($mock);
        $this->query->putPath('Where.Equal',
            $field
        );
        return $this;
    }

    /**
     * Get the amount of entries
     * @return int
     */
    public function count()
    {
        if ($this->query->contains('Selector')) {
            $this->query->setPath('Selector', 'Count(*)');
        } else {
            $selector = $this->query->getPath('Selector');
            $this->query->setPath('Selector', 'Count(' . $selector . ')');
        }

        $query = $this->getSelectQuery();
    }

    /**
     * Check if all entries match filter
     * @param $filter callable
     * @return bool
     */
    public function all($filter)
    {
        // TODO: Implement all() method.
    }

    /**
     * Execute callback for each entry
     * @param $callback callable
     * @return self
     */
    public function each($callback)
    {
        // TODO: Implement each() method.
    }

    /**
     * Check if data contains entry
     * @param $entry static
     * @return bool
     */
    public function contains($entry)
    {
        $data = $this->fetch();

        if ($data === null) {
            return false;
        }

        if (is_array($data)) {

        }


    }

    /**
     * Filter unique data
     * @return self
     */
    public function distinct()
    {
        $this->query->setPath('Distinct', true);
        return $this;
    }

    /**
     * Everything except provided entries
     * @param $entryOrArray mixed
     * @return self
     */
    public function except($entryOrArray)
    {
        // TODO: Implement except() method.
    }

    /**
     * Supply function to sort data
     * @param $comparable callable (Repository, int)
     * @return self
     */
    public function orderBy($comparable)
    {
        $fieldName = $comparable($this->generateMockInstance());
        $this->query->setPath('Order', $fieldName);
        return $this;
    }

    /**
     * Set order direction to 'ascending'
     */
    public function ascending()
    {
        $this->query->setPath('Direction', 'ASC');
        return $this;
    }

    /**
     * Set order direction to 'ascending'
     */
    public function descending()
    {
        $this->query->setPath('Direction', 'DESC');
        return $this;
    }

    /**
     * Reverse data
     * @return self
     */
    public function reverse()
    {
        // TODO: Implement reverse() method.
    }

    public function delete()
    {
        $tableDefinition = self::getTableDefinition();
        $primaryKeys = $tableDefinition->getPrimaryKeys();

        if (count($primaryKeys) == 0) {
            // Oh snap, no primary keys... let's just use ALL FIELDS MWAHAHA
            $primaryKeys = $tableDefinition->getFieldDefinitions();
        }

        foreach ($primaryKeys as $fieldDefinition) {
            $fieldName = $fieldDefinition->getFieldName();
            $this->where(function ($mock) use ($fieldName) {
                return $mock->$fieldName;
            }, $this->getFieldValue($fieldName));
        }
        $query = $this->getDeleteQuery();

        return self::getPDO()->execute($query[0], $query[1]);
    }

    public function getFieldValue
    (
        $fieldName
    )
    {
        $reflectionClass = new \ReflectionClass(get_called_class());

        if (!$reflectionClass->hasProperty($fieldName)) {
            return null;
        }

        $property = $reflectionClass->getProperty($fieldName);
        $property->setAccessible(true);
        return $property->getValue($this);
    }

    public function getDeleteQuery()
    {
        return $this->queryFlavor->generateDelete(
            $this->query->asArray()
        );
    }

    public function update()
    {
        $tableDefinition = self::getTableDefinition();
        $fieldDefinitions = $tableDefinition->getFieldDefinitions();

        $primaryKeys = $tableDefinition->getPrimaryKeys();

        if (count($primaryKeys) == 0) {
            // Oh snap, no primary keys... let's just use ALL FIELDS MWAHAHA
            $primaryKeys = $tableDefinition->getFieldDefinitions();
        }
        $selectorData = [];

        foreach ($primaryKeys as $fieldDefinition) {
            $fieldName = $fieldDefinition->getFieldName();
            $fieldValue = $this->getFieldValue($fieldName);

            if (array_key_exists($fieldName, $this->updateOverrides)) {
                $fieldValue = $this->updateOverrides[$fieldName];
            }
            $selectorData[$fieldName] = $fieldValue;
            $this->where(function ($mock) use ($fieldName) {
                return $mock->$fieldName;
            }, $fieldValue);
        }


        $selectorFields = [];
        $multiConstraints = [];

        foreach ($fieldDefinitions as $fieldDefinition) {

            if ($this->getFieldValue($fieldDefinition->getFieldName()) === null) {
                continue;
            }
            $selectorData['updated_' . $fieldDefinition->getFieldName()] = $this->getFieldValue($fieldDefinition->getFieldName());

            if ($fieldDefinition->isForeignKey()) {

                if ($fieldDefinition->hasHelperTable()) {
                    array_push($multiConstraints, $fieldDefinition);
                } else {
                    /**
                     * @var MysqlTable $remoteObject
                     */
                    $remoteObject = $this->getFieldValue($fieldDefinition->getFieldName());
                    $selectorData['updated_' . $fieldDefinition->getFieldName()] = $remoteObject->getFieldValue($fieldDefinition->getForeignColumn());
                }
            }
            array_push($selectorFields, $fieldDefinition->getFieldName());
        }

        $this->query->setPath('Fields', $selectorFields);

        $query = $this->getUpdateQuery();

        if (self::getPDO()->execute($query[0], $selectorData) !== false) {
            foreach ($this->updateOverrides as $overrideKey => $val) {
                $this->updateOverrides[$overrideKey] = $this->getFieldValue($overrideKey);
            }
        }


        /**
         * @var FieldDefinition $fieldDefinition
         */
        foreach ($multiConstraints as $fieldDefinition) {
            /**
             * @var MysqlTable $remoteObject
             */
            $remoteObjects = $this->getFieldValue($fieldDefinition->getFieldName());
            $helperTable = $fieldDefinition->getHelperTableDefinition();
            $this->resetQuery();
            $this->query->setPath('Ignore', true);
            $this->query->setPath('Table', $helperTable->getTableName());

            // Resetted to Helper Table
            $this->where
            (
                function ($mock) use ($tableDefinition, $fieldDefinition) {
                    return $tableDefinition->getField($fieldDefinition->getLocalHelperColumn())->getHelperColumnName();
                }, $this->getFieldValue($fieldDefinition->getLocalHelperColumn())
            );

            $query = $this->getDeleteQuery();

            echo $query[0] . '<br>';
            var_dump($query[1]) . '<br>';

            self::getPDO()->execute($query[0], $query[1]);
            $this->resetQuery();
            $this->query->setPath('Ignore', true);
            $this->query->setPath('Table', $helperTable->getTableName());

            $selectorFields = [];
            foreach ($helperTable->getFieldDefinitions() as $fieldDef) {
                array_push($selectorFields, $fieldDef->getFieldName());
            }

            $this->query->setPath('Fields', $selectorFields);

            $this->select(function ($mock) use ($selectorFields) {
                return $selectorFields;
            });

            // Setting Selectors

            $query = $this->getInsertQuery();

            foreach ($remoteObjects as $remoteObject) {

                self::getPDO()->execute($query[0],
                    [
                        $tableDefinition->getField($fieldDefinition->getLocalHelperColumn())->getHelperColumnName() => $this->getFieldValue($fieldDefinition->getLocalHelperColumn()),
                        $fieldDefinition
                            ->getForeignTableDefinition()
                            ->getField
                            (
                                $fieldDefinition->getForeignHelperColumn()
                            )
                            ->getHelperColumnName() => $remoteObject->getFieldValue($fieldDefinition->getForeignHelperColumn())
                    ]
                );
            }
        }


        return $this;
    }

    public function getUpdateQuery()
    {
        return $this->queryFlavor->generateUpdate(
            $this->query->asArray()
        );
    }

    public function getInsertQuery()
    {
        return $this->queryFlavor->generateInsert(
            $this->query->asArray()
        );
    }

    public function create
    (
        $skipExisting = false
    )
    {
        $tableDefinition = self::getTableDefinition();
        $fieldDefinitions = $tableDefinition->getFieldDefinitions();

        $selectorFields = [];
        $selectorData = [];

        $multiConstraints = [];

        foreach ($fieldDefinitions as $fieldDefinition) {

            if ($this->getFieldValue($fieldDefinition->getFieldName()) === null) {
                continue;
            }

            if ($fieldDefinition->isForeignKey()) {

                if ($fieldDefinition->hasHelperTable()) {
                    array_push($multiConstraints, $fieldDefinition);
                } else {
                    array_push($selectorFields, $fieldDefinition->getFieldName());
                    /**
                     * @var MysqlTable $remoteObject
                     */
                    $remoteObject = $this->getFieldValue($fieldDefinition->getFieldName());
                    $selectorData[$fieldDefinition->getFieldName()] = $remoteObject->getFieldValue($fieldDefinition->getForeignColumn());
                }

            } else {
                $selectorData[$fieldDefinition->getFieldName()] = $this->getFieldValue($fieldDefinition->getFieldName());
                array_push($selectorFields, $fieldDefinition->getFieldName());
            }

        }

        $this->query->setPath('Fields', $selectorFields);
        $this->query->setPath('Ignore', $skipExisting);
        $this->select(function ($mock) use ($selectorFields) {
            return $selectorFields;
        });
        $query = $this->getInsertQuery();

        if (self::getPDO()->execute($query[0], $selectorData) !== false) {
            if ($fieldDefinition = $tableDefinition->getAutoIncrementField()) {
                $this->setFieldValue($tableDefinition->getAutoIncrementField()->getFieldName(), self::getPDO()->getPdoInstance()->lastInsertId());
            }
        }

        /**
         * @var FieldDefinition $fieldDefinition
         */
        foreach ($multiConstraints as $fieldDefinition) {
            /**
             * @var MysqlTable $remoteObject
             */
            $remoteObjects = $this->getFieldValue($fieldDefinition->getFieldName());
            $helperTable = $fieldDefinition->getHelperTableDefinition();
            $this->resetQuery();
            $this->query->setPath('Table', $helperTable->getTableName());

            // Resetted to Helper Table

            $selectorFields = [];
            foreach ($helperTable->getFieldDefinitions() as $fieldDef) {
                array_push($selectorFields, $fieldDef->getFieldName());
            }

            $this->query->setPath('Fields', $selectorFields);

            $this->select(function ($mock) use ($selectorFields) {
                return $selectorFields;
            });

            // Setting Selectors

            $query = $this->getInsertQuery();

            foreach ($remoteObjects as $remoteObject) {

                self::getPDO()->execute($query[0],
                    [
                        $tableDefinition->getField($fieldDefinition->getLocalHelperColumn())->getHelperColumnName() => $this->getFieldValue($fieldDefinition->getLocalHelperColumn()),
                        $fieldDefinition
                            ->getForeignTableDefinition()
                            ->getField
                            (
                                $fieldDefinition->getForeignHelperColumn()
                            )
                            ->getHelperColumnName() => $remoteObject->getFieldValue($fieldDefinition->getForeignHelperColumn())
                    ]
                );
            }
        }


        return $this;
    }

    public function setFieldValue
    (
        $fieldName,
        $value
    )
    {
        $reflectionClass = new \ReflectionClass(get_called_class());

        if (!$reflectionClass->hasProperty($fieldName)) {
            return null;
        }

        $property = $reflectionClass->getProperty($fieldName);
        $property->setAccessible(true);
        $property->setValue($this, $value);
    }

    /**
     * Return filtered data as dictionary
     * @param $keyValueAssignment callable key value assigner
     * @return array;
     */
    public function asDictionary($keyValueAssignment)
    {
        // TODO: Implement asDictionary() method.
    }

    /**
     * Return filtered data as array
     * @return array
     */
    public function asArray()
    {
        // TODO: Implement asArray() method.
    }
}