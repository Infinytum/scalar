<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 8/13/17
 * Time: 10:57 PM
 */

namespace Scalar\Database\Table;


use Scalar\Util\ScalarArray;

class TableDefinition
{

    const TABLE_NAME = 'Table.Table';
    const TABLE_FIELDS = 'Fields';

    private $array;

    public function __construct
    (
        $array
    )
    {
        if (is_array($array) && !$array instanceof ScalarArray) {
            $array = new ScalarArray($array);
        } else if (!$array instanceof ScalarArray) {
            throw new \RuntimeException
            (
                'Invalid object passed to table definition'
            );
        }
        $this->array = $array;
    }

    /**
     * Get table name
     *
     * @return string|null
     */
    public function getTableName()
    {
        return $this->array->getPath(self::TABLE_NAME);
    }

    /**
     * Get fields defined in this table
     *
     * @return array Empty array if none
     */
    public function getFields()
    {
        return $this->array->getPath(self::TABLE_FIELDS, []);
    }

    /**
     * Returns all primary keys for this table
     *
     * @return FieldDefinition[]
     */
    public function getPrimaryKeys()
    {
        $fields = [];

        foreach ($this->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->isPrimaryKey()) {
                array_push($fields, $fieldDefinition);
            }
        }
        return $fields;
    }

    /**
     * Get fields defined in this table
     *
     * @return FieldDefinition[] Empty array if none
     */
    public function getFieldDefinitions()
    {
        $definitions = [];
        foreach ($this->array->getPath(self::TABLE_FIELDS, []) as $elementKey => $element) {
            array_push($definitions, $this->getField($elementKey));
        }
        return $definitions;
    }

    public function getField
    (
        $fieldName
    )
    {
        if (!$this->array->containsPath(self::TABLE_FIELDS . '.' . $fieldName)) {
            return null;
        }

        return new FieldDefinition
        (
            $fieldName,
            $this,
            $this->array->getPath(self::TABLE_FIELDS . '.' . $fieldName)
        );
    }

    /**
     * @return FieldDefinition|null
     */
    public function getAutoIncrementField()
    {
        foreach ($this->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->isAutoIncrement()) {
                return $fieldDefinition;
            }
        }
    }

    /**
     * Returns all tables which this table depends on
     *
     * @return TableDefinition[]
     */
    public function getDependencies()
    {
        $dependencies = [];

        foreach ($this->getFieldDefinitions() as $fieldDefinition) {

            if (!$fieldDefinition->hasHelperTable() && $fieldDefinition->isForeignKey()) {
                array_push($dependencies, $fieldDefinition->getForeignTableDefinition());
            }
        }
        return $dependencies;
    }

    /**
     * Returns all m:m relations for this table
     *
     * @return FieldDefinition[]
     */
    public function getMultiRelations()
    {
        $relations = [];
        foreach ($this->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->hasHelperTable()) {
                array_push($relations, $fieldDefinition);
            }
        }
        return $relations;
    }

}