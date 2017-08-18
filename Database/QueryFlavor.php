<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 13.06.17
 * Time: 13:07
 */

namespace Scaly\Database;


use Scaly\Config\IniConfig;
use Scaly\Database\Table\FieldDefinition;
use Scaly\Database\Table\TableDefinition;
use Scaly\Util\ScalyArray;

class QueryFlavor extends IniConfig
{
    const CONFIG_SELECT_BASE = 'Select.Base';
    const CONFIG_SELECT_ORDER = 'Select.Order';
    const CONFIG_SELECT_GROUP = 'Select.Group';
    const CONFIG_SELECT_LIMIT = 'Select.Limit';
    const CONFIG_SELECT_WHERE = 'Select.Where';
    const CONFIG_SELECT_WHERE_EQUAL = 'General.WhereEqual';
    const CONFIG_SELECT_WHERE_NOT_EQUAL = 'General.WhereNotEqual';
    const CONFIG_SELECT_WHERE_LIKE = 'General.WhereLike';
    const CONFIG_SELECT_WHERE_NOT_LIKE = 'General.WhereNotLike';
    const CONFIG_SELECT_JOIN = 'Select.Join';
    const CONFIG_CREATE_BASE = 'Create.Base';
    const CONFIG_CREATE_COLUMN = 'Create.Column';
    const CONFIG_CREATE_PRIMARY_KEY = 'Create.PrimaryKey';
    const CONFIG_CREATE_FOREIGN_KEY = 'Create.ForeignKey';
    const CONFIG_DELETE_BASE = 'Delete.Base';
    const CONFIG_DELETE_WHERE = 'Delete.Where';
    const CONFIG_INSERT_BASE = 'Insert.Base';
    const CONFIG_INSERT_IGNORE = 'Insert.Ignore';
    const CONFIG_INSERT_VALUES = 'Insert.Values';
    const CONFIG_UPDATE_BASE = 'Update.Base';
    const CONFIG_UPDATE_VALUES = 'Update.Values';
    const CONFIG_UPDATE_WHERE = 'Update.Where';

    private $injectableRegex = '/{(?<Path>[^}]*)}/x';

    private $placeholderCounter = 0;

    public function __construct($flavor)
    {
        parent::__construct
        (
            SCALY_CORE . '/Database/Query/' . $flavor . '.ini',
            [],
            true,
            INI_SCANNER_RAW
        );
    }

    /**
     * @param TableDefinition $tableDefinition
     * @return string
     */
    public function generateCreate
    (
        $tableDefinition
    )
    {
        $baseQuery = $this->get(self::CONFIG_CREATE_BASE);

        $columns = [];
        $primaryKeys = [];
        $constraints = [];

        $columnTemplate = $this->get(self::CONFIG_CREATE_COLUMN);

        foreach ($tableDefinition->getFieldDefinitions() as $fieldDefinition) {
            $column = [];

            array_push($column, $fieldDefinition->getEscapedFieldName());
            array_push($column, $fieldDefinition->getFieldType());

            if ($fieldDefinition->isNotNull()) {
                array_push($column, 'NOT NULL');
            }

            if ($fieldDefinition->isAutoIncrement()) {
                array_push($column, 'AUTO_INCREMENT');
            }

            if ($fieldDefinition->isPrimaryKey()) {
                array_push($primaryKeys, $fieldDefinition->getEscapedFieldName());
            }

            if ($fieldDefinition->isForeignKey() && !$fieldDefinition->hasHelperTable()) {
                array_push($constraints, $fieldDefinition);
            }

            array_push($columns, join(' ', $column));
        }

        if (count($primaryKeys) > 0) {
            $primaryTemplate = $this->get(self::CONFIG_CREATE_PRIMARY_KEY);

            array_push
            (
                $columns,
                $this->replacePlaceholders
                (
                    $primaryTemplate,
                    [
                        'PrimaryColumns' => join(', ', $primaryKeys)
                    ]
                )
            );
        }

        if (count($constraints) > 0) {
            $constraintTemplate = $this->get(self::CONFIG_CREATE_FOREIGN_KEY);

            /**
             * @var FieldDefinition $fieldDefinition
             */
            foreach ($constraints as $fieldDefinition) {
                array_push
                (
                    $columns,
                    $this->replacePlaceholders
                    (
                        $constraintTemplate,
                        [
                            'LeftColumn' => $fieldDefinition->getFieldName(),
                            'RemoteTable' => $fieldDefinition->getForeignTableDefinition()->getTableName(),
                            'RightColumn' => $fieldDefinition->getForeignColumn()
                        ]
                    )
                );
            }

        }

        return $this->replacePlaceholders($baseQuery, ['Table' => $tableDefinition->getTableName(), 'Columns' => join(', ', $columns)]);
    }

    private function replacePlaceholders($string, $placeholders)
    {
        preg_match_all
        (
            $this->injectableRegex,
            $string,
            $injectables,
            PREG_SET_ORDER,
            0
        );

        if (!$placeholders instanceof ScalyArray) {
            $placeholders = new ScalyArray($placeholders);
        }

        foreach ($injectables as $injectable) {
            $path = $injectable['Path'];
            if ($placeholders->contains($path)) {
                $string = str_replace($injectable[0], $placeholders[$path], $string);
            }
        }

        return $string;
    }

    public function generateInsert
    (
        $placeholders
    )
    {
        $placeholders = new ScalyArray($placeholders);
        $baseQuery = $this->get(self::CONFIG_INSERT_BASE);

        if ($placeholders->contains('Fields')) {
            $baseQuery = join(' ', [$baseQuery, $this->get(self::CONFIG_INSERT_VALUES)]);

            $preparedFields = [];
            foreach ($placeholders->getPath('Fields') as $field) {
                $placeholderKey = str_replace('.', '_', $field);
                array_push($preparedFields, ':' . $placeholderKey);
            }

            $placeholders->setPath('Values', join(', ', $preparedFields));
        }

        if ($placeholders->contains('Ignore') && $placeholders->getPath('Ignore')) {
            $placeholders->setPath('Ignore', $this->get(self::CONFIG_INSERT_IGNORE));
        }

        return [$this->replacePlaceholders($baseQuery, $placeholders)];
    }

    public function generateUpdate
    (
        $placeholders
    )
    {
        $placeholders = new ScalyArray($placeholders);
        $baseQuery = $this->get(self::CONFIG_UPDATE_BASE);

        if ($placeholders->contains("Filter")) {
            $baseQuery = join(' ', [$baseQuery, $this->get(self::CONFIG_UPDATE_WHERE)]);
        }

        if ($placeholders->contains('Fields')) {
            $preparedFields = [];
            foreach ($placeholders->getPath('Fields') as $field) {
                $conditionTemplate = $this->get(self::CONFIG_UPDATE_VALUES);
                $placeholderKey = str_replace('.', '_', $field);

                $parameters = new ScalyArray(['Column' => $field, 'Value' => ':updated_' . $placeholderKey]);
                array_push($preparedFields, $this->replacePlaceholders($conditionTemplate, $parameters));
            }

            $baseQuery = join(' ', [$baseQuery, join(', ', $preparedFields)]);
        }

        if ($placeholders->contains("Where")) {

            $whereArguments = new ScalyArray($placeholders->getPath('Where'));

            $conditions = [];

            if ($whereArguments->contains('Equal')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_EQUAL);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('Equal'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
            }

            if ($whereArguments->contains('NotEqual')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_NOT_EQUAL);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('NotEqual'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
            }

            if ($whereArguments->contains('Like')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_LIKE);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('Like'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
            }

            if ($whereArguments->contains('NotLike')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_NOT_LIKE);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('NotLike'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
            }

            $placeholders->setPath('Filter', join(' AND ', $conditions));

            $baseQuery = join(' ', [$baseQuery, $this->get(self::CONFIG_UPDATE_WHERE)]);
        }
        return [$this->replacePlaceholders($baseQuery, $placeholders)];
    }

    private function generateWhereFilter($array, $conditionTemplate)
    {
        $possibilities = [];
        $pdoPlaceholders = [];

        foreach (array_reverse($array) as $possibility) { // All possibilities which will be connected with OR
            $andConditions = [];
            foreach ($possibility as $key => $val) {

                if (!is_array($val)) {
                    $val = [$val];
                }

                $keys = [];

                foreach ($val as $whereOption) {
                    $placeholderKey = str_replace('.', '_', $key) . $this->placeholderCounter;
                    $pdoPlaceholders[$placeholderKey] = $whereOption;
                    $this->placeholderCounter++;
                    array_push($keys, ':' . $placeholderKey);
                }

                $parameters = new ScalyArray(['LeftColumn' => $key, 'RightColumn' => join(', ', $keys)]);
                array_push($andConditions, $this->replacePlaceholders($conditionTemplate, $parameters));
            }
            array_push($possibilities, '(' . join(' AND ', $andConditions) . ')');
        }

        return ['(' . join(' OR ', $possibilities) . ')', $pdoPlaceholders];
    }

    public function generateDelete
    (
        $placeholders
    )
    {
        $placeholders = new ScalyArray($placeholders);
        $baseQuery = $this->get(self::CONFIG_DELETE_BASE);
        $pdoData = [];

        if ($placeholders->contains("Filter")) {
            $baseQuery = join(' ', [$baseQuery, $this->get(self::CONFIG_DELETE_WHERE)]);
        }

        if ($placeholders->contains("Where")) {

            $whereArguments = new ScalyArray($placeholders->getPath('Where'));

            $conditions = [];

            if ($whereArguments->contains('Equal')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_EQUAL);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('Equal'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
                $pdoData = array_merge($pdoData, $whereFilter[1]);

            }

            if ($whereArguments->contains('NotEqual')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_NOT_EQUAL);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('NotEqual'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
                $pdoData = array_merge($pdoData, $whereFilter[1]);
            }

            if ($whereArguments->contains('Like')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_LIKE);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('Like'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
                $pdoData = array_merge($pdoData, $whereFilter[1]);
            }

            if ($whereArguments->contains('NotLike')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_NOT_LIKE);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('NotLike'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
                $pdoData = array_merge($pdoData, $whereFilter[1]);
            }

            $placeholders->setPath('Filter', join(' AND ', $conditions));

            $baseQuery = join(' ', [$baseQuery, $this->get(self::CONFIG_DELETE_WHERE)]);
        }

        return [$this->replacePlaceholders($baseQuery, $placeholders), $pdoData];
    }

    public function generateSelect
    (
        $placeholders
    )
    {
        $placeholders = new ScalyArray($placeholders);
        $baseQuery = $this->get(self::CONFIG_SELECT_BASE);
        $pdoData = [];

        if ($placeholders->contains("Join")) {
            foreach ($placeholders['Join'] as $join) {
                $join = new ScalyArray($join);
                if (
                    $join->contains('Column') &&
                    $join->contains('JoinColumn') &&
                    $join->contains('JoinType') &&
                    $join->contains('JoinOperator') &&
                    $join->contains('JoinTable')
                ) {
                    $baseQuery = join(
                        ' ',
                        [
                            $baseQuery,
                            $this->replacePlaceholders
                            (
                                $this->get
                                (
                                    self::CONFIG_SELECT_JOIN
                                ),
                                $join
                            )
                        ]
                    );
                }
            }
        }

        if ($placeholders->contains("Filter")) {
            $baseQuery = join(' ', [$baseQuery, $this->get(self::CONFIG_SELECT_WHERE)]);
        }

        $placeholders->setPath('Distinct', $placeholders->getPath('Distinct', false));


        if ($placeholders->contains("Where")) {

            $whereArguments = new ScalyArray($placeholders->getPath('Where'));

            $conditions = [];

            if ($whereArguments->contains('Equal')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_EQUAL);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('Equal'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
                $pdoData = array_merge($pdoData, $whereFilter[1]);

            }

            if ($whereArguments->contains('NotEqual')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_NOT_EQUAL);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('NotEqual'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
                $pdoData = array_merge($pdoData, $whereFilter[1]);
            }

            if ($whereArguments->contains('Like')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_LIKE);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('Like'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
                $pdoData = array_merge($pdoData, $whereFilter[1]);
            }

            if ($whereArguments->contains('NotLike')) {
                $conditionTemplate = $this->get(self::CONFIG_SELECT_WHERE_NOT_LIKE);
                $whereFilter = $this->generateWhereFilter($whereArguments->getPath('NotLike'), $conditionTemplate);
                array_push($conditions, $whereFilter[0]);
                $pdoData = array_merge($pdoData, $whereFilter[1]);
            }

            $placeholders->setPath('Filter', join(' AND ', $conditions));

            $baseQuery = join(' ', [$baseQuery, $this->get(self::CONFIG_SELECT_WHERE)]);
        }

        if ($placeholders->contains("Group")) {
            $baseQuery = join(' ', [$baseQuery, $this->get(self::CONFIG_SELECT_GROUP)]);
        }

        if ($placeholders->contains("Order") && $placeholders->contains("Direction")) {
            $baseQuery = join(' ', [$baseQuery, $this->get(self::CONFIG_SELECT_ORDER)]);
        }

        if ($placeholders->contains("Limit")) {
            $baseQuery = join(' ', [$baseQuery, $this->get(self::CONFIG_SELECT_LIMIT)]);
        }

        return [$this->replacePlaceholders($baseQuery, $placeholders), $pdoData];
    }

}
