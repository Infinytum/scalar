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

namespace Scalar\Database\Table;


use Scalar\Config\JsonConfig;
use Scalar\Database\PDODatabase;
use Scalar\Database\Query\Flavor;
use Scalar\Database\Query\FlavoredQuery;
use Scalar\IO\File;
use Scalar\Util\Annotation\PHPDoc;
use Scalar\Util\ScalarArray;

abstract class DatabaseTable implements \ArrayAccess
{

    #region Variables

    /**
     * Cache for reducing IO load when accessing table definitions
     *
     * @var ScalarArray
     */
    private static $tableDefinitions;

    /**
     * Array containing query parameters passed to the flavor query generator
     *
     * @var ScalarArray
     */
    private $query;

    /**
     * Dynamic query generator for supporting multiple languages
     *
     * @var Flavor
     */
    private $queryFlavor;

    /**
     * Hard-overrides for update queries to address issues related to primary key changes
     *
     * @var array
     */
    private $updateOverrides;

    #endregion

    #region Constructor

    /**
     * DatabaseTable constructor.
     * @param string $queryLang Query flavor name
     */
    public function __construct($queryLang)
    {

        $tableDefinition = self::getTableDefinition($this);
        $this->updateOverrides = [];

        foreach ($tableDefinition->getPrimaryKeys() as $fieldDefinition) {
            $this->updateOverrides[$fieldDefinition->getFieldName()] = $this->getPropertyValue($fieldDefinition->getFieldName());
        }

        $this->reset();
        $this->queryFlavor = Flavor::byName($queryLang);
    }

    #endregion

    #region Query Helper Methods

    /**
     * Add where filter to query
     *
     * @param string $wherePath
     * @param array $whereFilterData
     */
    private function addWhereFilter($wherePath, $whereFilterData)
    {
        $this->query->putPath($wherePath,
            $whereFilterData
        );
    }

    /**
     * Generates the delete query with the current parameters
     *
     * @return FlavoredQuery
     */
    private function getDeleteQuery()
    {
        return $this->queryFlavor->generateDelete(
            $this->query->asArray()
        );
    }

    /**
     * Generates the insert query with the current parameters
     *
     * @return FlavoredQuery
     */
    private function getInsertQuery()
    {
        return $this->queryFlavor->generateInsert(
            $this->query->asArray()
        );
    }

    /**
     * Generates the select query with the current parameters
     *
     * @return FlavoredQuery
     */
    private function getSelectQuery()
    {
        return $this->queryFlavor->generateSelect(
            $this->query->asArray()
        );
    }

    /**
     * Generates the update query with the current parameters
     *
     * @return FlavoredQuery
     */
    private function getUpdateQuery()
    {
        return $this->queryFlavor->generateUpdate(
            $this->query->asArray()
        );
    }

    /**
     * Resets the query params to default
     */
    private function reset()
    {
        $tableDefinition = self::getTableDefinition($this);
        $this->query = new ScalarArray
        (
            [
                'Table' => $tableDefinition->getTableName(),
                'Selector' => '*'
            ]
        );
    }

    #endregion

    #region Filtering Methods

    /**
     * Only return unique entries
     *
     * @return self
     */
    public function distinct()
    {
        $this->query->setPath('Distinct', true);
        return $this;
    }

    /**
     * Set selector for this query
     *
     * @param $lambda callable filter
     * @return self
     */
    public function select($lambda)
    {
        $field = $lambda
        (
            $this->generateMockInstance()
        );

        if (is_array($field)) {
            $field = join(', ', $field);
        }

        $this->query->setPath('Selector', $field);
        return $this;
    }

    /**
     * Only return data which matches your filter
     *
     * @param \Closure $lambda callable filter
     * @return self
     */
    public function where($lambda)
    {
        $this->addWhereFilter(
            'Where.Equal',
            $lambda
            (
                $this->generateMockInstance()
            )
        );
        return $this;
    }

    /**
     * Only return data which doesn't match your filter
     *
     * @param \Closure $lambda callable filter
     * @return self
     */
    public function whereNot($lambda)
    {
        $this->addWhereFilter(
            'Where.NotEqual',
            $lambda
            (
                $this->generateMockInstance()
            )
        );
        return $this;
    }

    /**
     * Only return data which is less than your filter
     *
     * @param \Closure $lambda callable filter
     * @return self
     */
    public function whereLess($lambda)
    {
        $this->addWhereFilter(
            'Where.Less',
            $lambda
            (
                $this->generateMockInstance()
            )
        );
        return $this;
    }

    /**
     * Only return data which is greater than your filter
     *
     * @param \Closure $lambda callable filter
     * @return self
     */
    public function whereGreater($lambda)
    {
        $this->addWhereFilter(
            'Where.Greater',
            $lambda
            (
                $this->generateMockInstance()
            )
        );
        return $this;
    }

    /**
     * Only return data which matches your filter expression
     *
     * @param \Closure $lambda callable filter
     * @return $this
     */
    public function whereLike($lambda)
    {
        $this->addWhereFilter(
            'Where.Like',
            $lambda
            (
                $this->generateMockInstance()
            )
        );
        return $this;
    }

    /**
     * Only return data which doesn't match your filter expression
     *
     * @param \Closure $lambda callable filter
     * @return $this
     */
    public function whereNotLike($lambda)
    {
        $this->addWhereFilter(
            'Where.NotLike',
            $lambda
            (
                $this->generateMockInstance()
            )
        );
        return $this;
    }

    #endregion

    #region Order/Sorting Methods

    /**
     * Set order direction to 'ascending'
     *
     * @return static
     */
    public function ascending()
    {
        $this->query->setPath('Direction', 'ASC');
        return $this;
    }

    /**
     * Set order direction to 'descending'
     *
     * @return static
     */
    public function descending()
    {
        $this->query->setPath('Direction', 'DESC');
        return $this;
    }

    /**
     * Sort entries according to specified field
     *
     * @param \Closure $comparable
     * @return self
     */
    public function orderBy($comparable)
    {
        $fieldName = $comparable($this->generateMockInstance());
        $this->query->setPath('Order', $fieldName);
        return $this;
    }


    #endregion

    #region Miscellaneous Methods

    /**
     * Limit the amount and/or starting point of entries returned
     *
     * @param int $from Start returning entries beginning at row with this number
     * @param int $to Amount of entries which should be returned starting from your starting point
     * @return $this
     */
    public function limit
    (
        $from = 0,
        $to
    )
    {
        $this->query->setPath('Limit.Offset', $from);
        $this->query->setPath('Limit.Count', $to);
        return $this;
    }

    #endregion

    #region Informative Methods

    /**
     * Check if any entry matched your current query
     *
     * @return bool
     */
    public function any()
    {
        return $this->count() > 0;
    }

    /**
     * Fetches the amount of entries that matches your current query
     *
     * @return int
     */
    public function count()
    {
        $selector = $this->query->getPath('Selector');
        $this->query->setPath('Selector', 'Count(' . $selector . ') as amount');

        $query = $this->getSelectQuery();
        $row = $this->getPDO()->execute($query->getQueryString(), $query->getQueryData())[0];
        return intval($row['amount']);
    }

    #endregion

    #region Table Control Methods

    /**
     * Create database entry
     *
     * @param bool $skipExisting Throw no error when entry already exists
     * @return $this
     */
    public function create($skipExisting = false)
    {
        $tableDefinition = self::getTableDefinition();
        $fieldDefinitions = $tableDefinition->getFieldDefinitions();

        $selectorFields = [];
        $selectorData = [];

        $multiConstraints = [];

        foreach ($fieldDefinitions as $fieldDefinition) {

            if ($this->getPropertyValue($fieldDefinition->getFieldName()) === null) {
                continue;
            }

            if ($fieldDefinition->isForeignKey()) {

                if ($fieldDefinition->hasHelperTable()) {
                    array_push($multiConstraints, $fieldDefinition);
                } else {
                    array_push($selectorFields, $fieldDefinition->getFieldName());
                    /**
                     * @var DatabaseTable $remoteObject
                     */
                    $remoteObject = $this->getPropertyValue($fieldDefinition->getFieldName());
                    $selectorData[$fieldDefinition->getFieldName()] = $remoteObject->getPropertyValue($fieldDefinition->getForeignColumn());
                }

            } else {
                $selectorData[$fieldDefinition->getFieldName()] = $this->getPropertyValue($fieldDefinition->getFieldName());
                array_push($selectorFields, $fieldDefinition->getFieldName());
            }

        }

        $this->query->setPath('Fields', $selectorFields);
        $this->query->setPath('Ignore', $skipExisting);
        $this->select(function ($mock) use ($selectorFields) {
            return $selectorFields;
        });
        $query = $this->getInsertQuery();

        if ($this->getPDO()->execute($query->getQueryString(), $selectorData) !== false) {
            if ($fieldDefinition = $tableDefinition->getAutoIncrementField()) {
                $this->setPropertyValue($tableDefinition->getAutoIncrementField()->getFieldName(), $this->getPDO()->getPdoInstance()->lastInsertId());
            }
        }

        /**
         * @var FieldDefinition $fieldDefinition
         */
        foreach ($multiConstraints as $fieldDefinition) {
            /**
             * @var DatabaseTable $remoteObject
             */
            $remoteObjects = $this->getPropertyValue($fieldDefinition->getFieldName());
            $helperTable = $fieldDefinition->getHelperTableDefinition();
            $this->reset();
            $this->query->setPath('Table', $helperTable->getTableName());

            $selectorFields = [];
            foreach ($helperTable->getFieldDefinitions() as $fieldDef) {
                array_push($selectorFields, $fieldDef->getFieldName());
            }

            $this->query->setPath('Fields', $selectorFields);

            $this->select(function ($mock) use ($selectorFields) {
                return $selectorFields;
            });

            $query = $this->getInsertQuery();

            foreach ($remoteObjects as $remoteObject) {

                if ($remoteObject instanceof DatabaseTable) {
                    $remoteObject = $remoteObject->getPropertyValue($fieldDefinition->getForeignHelperColumn());
                }

                $this->getPDO()->execute($query->getQueryString(),
                    [
                        $tableDefinition->getField($fieldDefinition->getLocalHelperColumn())->getHelperColumnName() => $this->getPropertyValue($fieldDefinition->getLocalHelperColumn()),
                        $fieldDefinition
                            ->getForeignTableDefinition()
                            ->getField
                            (
                                $fieldDefinition->getForeignHelperColumn()
                            )
                            ->getHelperColumnName() => $remoteObject
                    ]
                );
            }
        }


        return $this;
    }

    /**
     * Delete database entry
     *
     * @return boolean
     */
    public function delete()
    {
        $tableDefinition = self::getTableDefinition();
        $primaryKeys = $tableDefinition->getPrimaryKeys();

        if (count($primaryKeys) == 0) {
            $primaryKeys = $tableDefinition->getFieldDefinitions();
        }

        foreach ($primaryKeys as $fieldDefinition) {
            $fieldName = $fieldDefinition->getFieldName();
            $this->where(function ($mock) use ($fieldName) {
                return [$mock->$fieldName => $this->getPropertyValue($fieldName)];
            });
        }
        $query = $this->getDeleteQuery();

        return $this->getPDO()->execute($query->getQueryString(), $query->getQueryData());
    }

    /**
     * Fetch data with current query
     *
     * @return ScalarArray
     */
    public function fetch()
    {
        $tableDefinition = self::getTableDefinition();
        $query = $this->getSelectQuery();
        $rows = $this->getPDO()->execute($query->getQueryString(), $query->getQueryData());

        if (!$rows) {
            return new ScalarArray();
        }

        $data = [];

        foreach ($rows as $row) {

            foreach ($tableDefinition->getMultiRelations() as $fieldDefinition) {

                $this->reset();
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
                    function ($mock) use ($fieldDefinition, $tableDefinition, $row) {
                        return [
                            $tableDefinition
                                ->getField
                                (
                                    $fieldDefinition
                                        ->getLocalHelperColumn()
                                )
                                ->getHelperColumnName() => $row[$fieldDefinition->getLocalHelperColumn()]];
                    }
                );

                $query = $this->getSelectQuery();
                $result = $this->getPDO()->execute($query->getQueryString(), $query->getQueryData());

                $ids = [];
                $fieldName = $fieldDefinition->getForeignTableDefinition()->getTableName();
                $fieldName .= '_';
                $fieldName .= $fieldDefinition->getForeignHelperColumn();
                foreach ($result as $re) {
                    array_push($ids, $re[$fieldName]);
                }

                $row[$fieldDefinition->getFieldName()] = $ids;
            }
            array_push($data, self::fromRow($row));

        }
        return new ScalarArray($data);
    }

    /**
     * Update existing database entry
     *
     * @return $this
     */
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
            $fieldValue = $this->getPropertyValue($fieldName);

            if (array_key_exists($fieldName, $this->updateOverrides)) {
                $fieldValue = $this->updateOverrides[$fieldName];
            }
            $selectorData[$fieldName] = $fieldValue;
            $this->where(function ($mock) use ($fieldName, $fieldValue) {
                return [$mock->$fieldName => $fieldValue];
            });
        }


        $selectorFields = [];
        $multiConstraints = [];

        foreach ($fieldDefinitions as $fieldDefinition) {

            if ($this->getPropertyValue($fieldDefinition->getFieldName()) === null) {
                continue;
            }
            $selectorData['updated_' . $fieldDefinition->getFieldName()] = $this->getPropertyValue($fieldDefinition->getFieldName());

            if ($fieldDefinition->isForeignKey()) {

                if ($fieldDefinition->hasHelperTable()) {
                    array_push($multiConstraints, $fieldDefinition);
                } else {
                    /**
                     * @var DatabaseTable $remoteObject
                     */
                    $remoteObject = $this->getPropertyValue($fieldDefinition->getFieldName());
                    $selectorData['updated_' . $fieldDefinition->getFieldName()] = $remoteObject->getPropertyValue($fieldDefinition->getForeignColumn());
                }
            }
            array_push($selectorFields, $fieldDefinition->getFieldName());
        }

        $this->query->setPath('Fields', $selectorFields);

        $query = $this->getUpdateQuery();

        if ($this->getPDO()->execute($query->getQueryString(), $selectorData) !== false) {
            foreach ($this->updateOverrides as $overrideKey => $val) {
                $this->updateOverrides[$overrideKey] = $this->getPropertyValue($overrideKey);
            }
        }


        /**
         * @var FieldDefinition $fieldDefinition
         */
        foreach ($multiConstraints as $fieldDefinition) {
            /**
             * @var DatabaseTable $remoteObject
             */
            $remoteObjects = $this->getPropertyValue($fieldDefinition->getFieldName());
            $helperTable = $fieldDefinition->getHelperTableDefinition();
            $this->reset();
            $this->query->setPath('Ignore', true);
            $this->query->setPath('Table', $helperTable->getTableName());

            $this->where
            (
                function ($mock) use ($tableDefinition, $fieldDefinition) {
                    return [
                        $tableDefinition
                            ->getField
                            (
                                $fieldDefinition
                                    ->getLocalHelperColumn()
                            )
                            ->getHelperColumnName() => $this->getPropertyValue($fieldDefinition->getLocalHelperColumn())];
                });

            $query = $this->getDeleteQuery();

            $this->getPDO()->execute($query->getQueryString(), $query->getQueryData());
            $this->reset();
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

            $query = $this->getInsertQuery();

            foreach ($remoteObjects as $remoteObject) {

                $this->getPDO()->execute($query->getQueryString(),
                    [
                        $tableDefinition->getField($fieldDefinition->getLocalHelperColumn())->getHelperColumnName() => $this->getPropertyValue($fieldDefinition->getLocalHelperColumn()),
                        $fieldDefinition
                            ->getForeignTableDefinition()
                            ->getField
                            (
                                $fieldDefinition->getForeignHelperColumn()
                            )
                            ->getHelperColumnName() => $remoteObject->getPropertyValue($fieldDefinition->getForeignHelperColumn())
                    ]
                );
            }
        }


        return $this;
    }

    #endregion

    #region Reflection Methods

    /**
     * Get field value stored in this object
     *
     * @param string $fieldName Name of field
     * @return mixed|null
     */
    private function getPropertyValue($fieldName)
    {
        $reflectionClass = new \ReflectionClass(get_class($this));
        $tableDefinition = self::getTableDefinition($this);

        if (!$reflectionClass->hasProperty($fieldName)) {

            if ($reflectionClass->hasMethod($fieldName)) {
                $property = $reflectionClass->getMethod($fieldName);
                $property->setAccessible(true);
                return $property->invoke($this);
            }

            return null;
        }

        $fieldDefinition = $tableDefinition->getField($fieldName);
        $property = $reflectionClass->getProperty($fieldName);
        $property->setAccessible(true);
        $value = $property->getValue($this);

        if ($fieldDefinition->isLazyLoading()) {
            if (is_array($value)) {
                $data = [];
                foreach ($value as $item) {
                    array_push($data, $this->resolveDependency($fieldDefinition, $item));
                }
                $value = $data;
            } else {
                $value = $this->resolveDependency($fieldDefinition, $value);
            }
        }

        return $value;
    }

    /**
     * Create an reflection class instance for a table
     *
     * @param string|DatabaseTable $tableClass
     * @return \ReflectionClass
     */
    private static function getReflectionClass($tableClass)
    {
        if ($tableClass === null) {
            $tableClass = get_called_class();
        } else if ($tableClass instanceof DatabaseTable) {
            $tableClass = get_class($tableClass);
        }

        return new \ReflectionClass($tableClass);
    }

    /**
     * Set field value stored in this object
     *
     * @param string $fieldName Name of field
     * @param mixed $value
     */
    private function setPropertyValue($fieldName, $value)
    {
        $reflectionClass = new \ReflectionClass(get_called_class());

        if (!$reflectionClass->hasProperty($fieldName)) {
            return;
        }

        $property = $reflectionClass->getProperty($fieldName);
        $property->setAccessible(true);
        $property->setValue($this, $value);
    }

    #endregion

    #region Private Table Helper Methods

    /**
     * @param FieldDefinition $field
     * @param mixed $linkingValue
     */
    private function resolveDependency
    (
        $field,
        $linkingValue
    )
    {
        if ($field->isForeignKey()) {
            $fakeForeignTable = self::getFakeInstance($field->getForeignTableDefinition()->getTableClass());

            if ($field->hasHelperTable()) {
                return $fakeForeignTable->where(
                    function ($mock) use ($field, $linkingValue) {
                        return [
                            $field->getForeignHelperColumn() => $linkingValue
                        ];
                    }
                )->fetch()->firstOrDefault();
            } else {
                return $fakeForeignTable->where(
                    function ($mock) use ($field, $linkingValue) {
                        return [
                            $field->getForeignColumn() => $linkingValue
                        ];
                    }
                )->fetch()->firstOrDefault();
            }
        }
        return $linkingValue;
    }

    private function fromRow($row)
    {
        $tableDefinition = self::getTableDefinition($this);
        $reflectionTable = self::getReflectionClass($this);

        foreach ($tableDefinition->getFieldDefinitions() as $field) {

            if ($field->isLazyLoading()) {
                continue;
            }

            if ($field->isForeignKey()) {
                $fakeForeignTable = self::getFakeInstance($field->getForeignTableDefinition()->getTableClass());
                $fieldValues = $row[$field->getFieldName()];

                if ($field->hasHelperTable()) {
                    $data = [];
                    foreach ($fieldValues as $fieldValue) {
                        array_push($data, $this->resolveDependency($field, $fieldValue));
                    }
                    $row[$field->getFieldName()] = $data;
                } else {
                    $row[$field->getFieldName()] = $this->resolveDependency($field, $fieldValues);
                }
            }
        }

        return $reflectionTable->newInstanceArgs(array_values($row));
    }

    /**
     * @param string|DatabaseTable $tableClass
     * @return static|object
     */
    private function generateMockInstance($tableClass = null)
    {
        if ($tableClass === null) {
            $tableClass = get_called_class();
        } else if ($tableClass instanceof DatabaseTable) {
            $tableClass = get_class($tableClass);
        }

        $tableDefinition = self::getTableDefinition($tableClass);
        $fields = $tableDefinition->getFieldDefinitions();
        $fields = array_map(function ($definition) {
            return $definition->getFieldName();
        }, $fields);


        $foreignClass = new \ReflectionClass($tableClass);
        return $foreignClass->newInstanceArgs($fields);
    }

    /**
     * Get PDO instance for this table
     *
     * @return PDODatabase|null
     */
    private function getPDO()
    {
        return PDODatabase::getPDO(self::getTableDefinition($this)->getDatabase());
    }

    /**
     * Passed field will be updated with previous method's first argument
     * If none is passed, nothing happens
     *
     * @param mixed $field Field to be modified
     * @param mixed $valueOverride
     * @return mixed Fields value
     */
    protected function property(&$field, $valueOverride = null)
    {
        $trace = debug_backtrace(0)[1];

        if (count($trace['args']) >= 1) {
            $field = $trace['args'][0];
        }

        if (func_num_args() > 1) {
            $field = $valueOverride;
        }

        return $field;
    }

    #endregion

    #region Public Table Helper Methods

    /**
     * Get fake instance of this class for non-modifying actions
     *
     * @param string $tableClass Full class name of the table you want a fake instance of
     * @return object|static
     */
    public static function getFakeInstance($tableClass = null)
    {
        if ($tableClass === null) {
            $tableClass = get_called_class();
        }

        $foreignClass = new \ReflectionClass($tableClass);
        return $foreignClass->newInstanceArgs(array_fill(0, $foreignClass->getConstructor()->getNumberOfParameters(), null));
    }

    /**
     * Get table definition
     *
     * @param string|DatabaseTable $tableClass Full class name of the table you want the table definition of
     * @return null|TableDefinition
     */
    public static function getTableDefinition($tableClass = null)
    {
        if (!self::$tableDefinitions) {
            self::$tableDefinitions = new ScalarArray();
        }

        if ($tableClass === null) {
            $tableClass = get_called_class();
        } else if ($tableClass instanceof DatabaseTable) {
            $tableClass = get_class($tableClass);
        }

        $reflectionClass = new \ReflectionClass($tableClass);
        $definitionPath = dirname($reflectionClass->getFileName()) . '/' . $reflectionClass->getShortName() . '.json';
        $cachePath = $reflectionClass->getName();

        if (self::$tableDefinitions->containsPath($cachePath)) {
            return self::$tableDefinitions->getPath($cachePath);
        }

        $definitionLoader = new JsonConfig(new File($definitionPath, true), []);
        $definitionLoader->load();

        if (!$definitionLoader->has('Table')) {

            $reflectionFields = $reflectionClass->getProperties();

            $definitionLoader->set('Table', (new PHPDoc($reflectionClass))->getAnnotations());

            if (!$definitionLoader->hasPath('Table.Table') || !$definitionLoader->hasPath('Table.Database')) {
                return null;
            }

            $definitionLoader->setPath('Table.Class', $reflectionClass->getName());

            foreach ($reflectionFields as $reflectionField) {
                $params = (new PHPDoc($reflectionField))->getAnnotations();
                if (array_key_exists('Field', $params)) {
                    $definitionLoader->setPath('Fields.' . $reflectionField->name, $params);
                }

            }
            $definitionLoader->save();
        }

        $tableDefinition = new TableDefinition($definitionLoader->asScalarArray());

        self::$tableDefinitions->setPath($cachePath, $tableDefinition);

        return $tableDefinition;
    }

    #endregion

    #region Built-in Shorthands

    /**
     * Return filtered data as array
     * @return array
     */
    public function asArray()
    {
        return $this->fetch()->asArray();
    }

    /**
     * Return filtered data as dictionary
     *
     * @param $keyValueAssignment callable key value assigner
     * @return array
     */
    public function asDictionary($keyValueAssignment)
    {
        return $this->fetch()->asDictionary($keyValueAssignment);
    }

    /**
     * Fetch all entries in the database
     *
     * @return ScalarArray
     */
    public static function fetchAll()
    {
        $fakeInstance = self::getFakeInstance();
        $fakeInstance->reset();
        return $fakeInstance->fetch();
    }

    #endregion

    #region ArrayAccess

    public function offsetExists($offset)
    {
        return $this->offsetGet($offset) !== null;
    }

    public function offsetGet($offset)
    {
        if (substr($offset, -2) === '()') {
            $offset = substr($offset, 0, -2);
            $reflectionClass = new \ReflectionClass(get_called_class());

            if (!$reflectionClass->hasMethod($offset)) {
                return null;
            }

            $property = $reflectionClass->getMethod($offset);
            $property->setAccessible(true);
            return $property->invoke($this);
        }

        return $this->getPropertyValue($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setPropertyValue($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->setPropertyValue($offset, null);
    }

    #endregion

}