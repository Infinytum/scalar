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

class FieldDefinition
{

    const FIELD_TYPE = 'FieldType';
    const FIELD_NOT_NULL = 'NotNull';
    const FIELD_PRIMARY_KEY = 'PrimaryKey';
    const FIELD_UNIQUE = 'Unique';
    const FIELD_AUTO_INCREMENT = 'AutoIncrement';

    const FIELD_HAS = 'Has';
    const FIELD_HAS_MANY = 'HasMany';

    const FOREIGN_TABLE = 0;
    const FOREIGN_KEY = 1;
    const LOCAL_KEY = 2;

    private $array;

    private $fieldName;

    private $tableDefinition;

    public function __construct
    (
        $fieldName,
        $tableDefinition,
        $array
    )
    {
        if (is_array($array) && !$array instanceof ScalarArray) {
            $array = new ScalarArray($array);
        } else if (!$array instanceof ScalarArray) {
            throw new \RuntimeException
            (
                'Invalid field definition array passed to field definition'
            );
        }
        if (!$tableDefinition instanceof TableDefinition) {
            throw new \RuntimeException
            (
                'Invalid table definition passed to field definition'
            );
        }
        $this->array = $array;
        $this->fieldName = $fieldName;
        $this->tableDefinition = $tableDefinition;
    }

    public function getEscapedFieldName()
    {
        return '`' . $this->fieldName . '`';
    }

    /**
     * Determine if this field is a primary key
     *
     * @return bool
     */
    public function isPrimaryKey()
    {
        return $this->array->getPath(self::FIELD_PRIMARY_KEY, false);
    }

    /**
     * Determine if this field is auto-incrementing
     *
     * @return bool
     */
    public function isAutoIncrement()
    {
        return $this->array->getPath(self::FIELD_AUTO_INCREMENT, false);
    }

    /**
     * Determine if this field is unique
     *
     * @return bool
     */
    public function isUnique()
    {
        return $this->array->getPath(self::FIELD_UNIQUE, false);
    }

    /**
     * Determine if this field is allowed to be null
     *
     * @return bool
     */
    public function isNotNull()
    {
        return $this->array->getPath(self::FIELD_NOT_NULL, true);
    }

    /**
     * Determine if this field is a foreign key
     *
     * @return bool
     */
    public function isForeignKey()
    {
        return $this->array->containsPath(self::FIELD_HAS) || $this->array->containsPath(self::FIELD_HAS_MANY);
    }

    /**
     * Determine if this field has a complex relation to another table
     *
     * @return bool
     */
    public function hasHelperTable()
    {
        return $this->array->containsPath(self::FIELD_HAS_MANY);
    }

    /**
     * Determine to which foreign column this field is related
     *
     * @return mixed
     */
    public function getForeignColumn()
    {
        return $this->array->getPath(self::FIELD_HAS)[self::FOREIGN_KEY];
    }

    /**
     * @return TableDefinition
     */
    public function getHelperTableDefinition()
    {
        $foreignTable = $this->getForeignTableDefinition();
        $helperMock =
            [
                'Table' => [
                    'Table' => $this->getHelperTable()
                ],
                'Fields' => [
                    $this->tableDefinition->getTableName() . '_' . $this->tableDefinition->getField($this->getLocalHelperColumn())->getFieldName() => [
                        'FieldType' => $this->tableDefinition->getField($this->getLocalHelperColumn())->getFieldType(),
                        'NotNull' => true,
                        'PrimaryKey' => true,
                        'Has' => [
                            $this->tableDefinition->getTableName(),
                            $this->getLocalHelperColumn()
                        ]
                    ],
                    $foreignTable->getTableName() . '_' . $this->getForeignHelperColumn() => [
                        'FieldType' => $foreignTable->getField($this->getForeignHelperColumn())->getFieldType(),
                        'NotNull' => true,
                        'PrimaryKey' => true,
                        'Has' => [
                            $this->getForeignTableDefinition()->getTableName(),
                            $this->getForeignHelperColumn()
                        ]
                    ]
                ]
            ];
        return new TableDefinition($helperMock);
    }

    /**
     * Get foreign table this field is related to
     *
     * @return TableDefinition
     */
    public function getForeignTableDefinition()
    {
        $reflectionClass = new \ReflectionClass('Scalar\App\Database\\' . MysqlTable::getPDO()->getName() . '\\' . $this->getForeignTable());
        return $reflectionClass->getMethod('getTableDefinition')->invoke(null);
    }

    /**
     * Get foreign table this field is related to
     *
     * @return string
     */
    public function getForeignTable()
    {
        return $this->array->getPath(self::FIELD_HAS, $this->array->getPath(self::FIELD_HAS_MANY))[self::FOREIGN_TABLE];
    }

    /**
     * Determine name of helper table
     *
     * @return string
     */
    public function getHelperTable()
    {
        $helperTable = [$this->getForeignTableDefinition()->getTableName(), $this->tableDefinition->getTableName()];
        sort($helperTable);
        return join('_', $helperTable);
    }

    /**
     * Get Field name
     *
     * @return string|null
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Determine which key is used in the helper table for referencing at our table
     *
     * @return mixed
     */
    public function getLocalHelperColumn()
    {
        return $this->array->getPath(self::FIELD_HAS_MANY)[self::LOCAL_KEY];
    }

    /**
     * Get sql type of field
     *
     * @param string $default
     * @return string
     */
    public function getFieldType
    (
        $default = 'TEXT'
    )
    {
        return $this->array->getPath(self::FIELD_TYPE, $default);
    }

    /**
     * Determine to which foreign column is used in the helper table
     *
     * @return mixed
     */
    public function getForeignHelperColumn()
    {
        return $this->array->getPath(self::FIELD_HAS_MANY)[self::FOREIGN_KEY];
    }

    public function getHelperColumnName()
    {
        $fieldName = $this->tableDefinition->getTableName();
        $fieldName .= '_';
        $fieldName .= $this->getFieldName();
        return $fieldName;
    }

}