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
 * Date: 8/13/17
 * Time: 10:57 PM
 */

namespace Scalar\Database\Table;

use Scalar\Util\ScalarArray;

class TableDefinition
{

    const TABLE_DATABASE = 'Table.Database';
    const TABLE_NAME = 'Table.Table';
    const TABLE_CLASS = 'Table.Class';
    const TABLE_FIELDS = 'Fields';

    private $array;

    private $fieldDefinitions;

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
        $this->fieldDefinitions = new ScalarArray();
    }

    /**
     * Get table database
     *
     * @return string|null
     */
    public function getDatabase()
    {
        return $this->array->getPath(self::TABLE_DATABASE);
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
     * Get table name
     *
     * @return string|null
     */
    public function getTableClass()
    {
        return $this->array->getPath(self::TABLE_CLASS);
    }

    public function getTableClassName()
    {
        $className = $this->getTableClass();
        $className = str_replace('Scalar\\App\\Database\\' . $this->getDatabase() . "\\", '', $className);
        return str_replace('.php', '', $className);
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

        if ($this->fieldDefinitions->containsPath(self::TABLE_FIELDS . '.' . $fieldName)) {
            return $this->fieldDefinitions->getPath(self::TABLE_FIELDS . '.' . $fieldName);
        }

        $fieldDefintion = new FieldDefinition
        (
            $fieldName,
            $this,
            $this->array->getPath(self::TABLE_FIELDS . '.' . $fieldName)
        );

        $this->fieldDefinitions->setPath(self::TABLE_FIELDS . '.' . $fieldName, $fieldDefintion);

        return $fieldDefintion;
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

        return null;
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