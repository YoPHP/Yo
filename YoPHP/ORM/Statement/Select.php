<?php

namespace YoPHP\ORM\Statement;

use YoPHP\ORM\Clause\Limit;
use YoPHP\ORM\Clause\Order;
use YoPHP\ORM\Clause\Where;
use YoPHP\ORM\Clause\Group;
use YoPHP\ORM\Clause\Having;
use YoPHP\ORM\Clause\Join;
use YoPHP\ORM\Clause\Offset;

/**
 * Class SelectStatement
 */
class Select extends Statement {

    /**
     * @var bool
     */
    protected $distinct = false;

    /**
     * @var bool
     */
    protected $aggregate = false;

    /**
     * @var Join
     */
    protected $Join;

    /**
     * @var Group
     */
    protected $Group;

    /**
     * @var Having
     */
    protected $Having;

    /**
     * @var Offset
     */
    protected $Offset;

    public function __construct(Where $where, Order $order, Limit $limit, Join $join, Group $group, Having $having, Offset $offset) {
        parent::__construct($where, $order, $limit);
//        parent::__construct($dbh);
//        !empty($columns) && $this->setColumns(is_array($columns) ? $columns : explode(',', $columns));
//        !empty($table) && $this->from($table);
        $this->Join = $join;
        $this->Group = $group;
        $this->Having = $having;
        $this->Offset = $offset;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function columns($columns) {
        $this->setColumns(is_array($columns) ? $columns : explode(',', $columns));
        return $this;
    }

    /**
     * @return $this
     */
    public function distinct() {
        $this->distinct = true;

        return $this;
    }

    /**
     * @param string $column
     * @param null   $as
     * @param bool   $distinct
     *
     * @return $this
     */
    public function count($column = '*', $as = null, $distinct = false) {
        $this->aggregate = true;

        $this->columns[] = $this->setDistinct($distinct) . ' ' . $this->query->identifier($column) . ' )' . $this->setAs($as);

        return $this;
    }

    /**
     * @param string $column
     * @param null   $as
     *
     * @return $this
     */
    public function distinctCount($column = '*', $as = null) {
        $this->count($column, $as, true);

        return $this;
    }

    /**
     * @param $column
     * @param null $as
     *
     * @return $this
     */
    public function max($column, $as = null) {
        $this->aggregate = true;

        $this->columns[] = 'MAX( ' . $this->query->identifier($column) . ' )' . $this->setAs($as);

        return $this;
    }

    /**
     * @param $column
     * @param null $as
     *
     * @return $this
     */
    public function min($column, $as = null) {
        $this->aggregate = true;

        $this->columns[] = 'MIN( ' . $this->query->identifier($column) . ' )' . $this->setAs($as);

        return $this;
    }

    /**
     * @param $column
     * @param null $as
     *
     * @return $this
     */
    public function avg($column, $as = null) {
        $this->aggregate = true;

        $this->columns[] = 'AVG( ' . $this->query->identifier($column) . ' )' . $this->setAs($as);

        return $this;
    }

    /**
     * @param $column
     * @param null $as
     *
     * @return $this
     */
    public function sum($column, $as = null) {
        $this->aggregate = true;

        $this->columns[] = 'SUM( ' . $this->query->identifier($column) . ' )' . $this->setAs($as);

        return $this;
    }

    /**
     * @param $table
     * @param $first
     * @param null   $operator
     * @param null   $second
     * @param string $joinType
     *
     * @return $this
     */
    public function join($table, $first, $operator = null, $second = null, $joinType = 'INNER') {
        $this->Join->join($table, $first, $operator, $second, $joinType);

        return $this;
    }

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     *
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null) {
        $this->Join->leftJoin($table, $first, $operator, $second);

        return $this;
    }

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     *
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null) {
        $this->Join->rightJoin($table, $first, $operator, $second);

        return $this;
    }

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     *
     * @return $this
     */
    public function fullJoin($table, $first, $operator = null, $second = null) {
        $this->Join->fullJoin($table, $first, $operator, $second);

        return $this;
    }

    /**
     * @param $columns
     *
     * @return $this
     */
    public function groupBy($columns) {
        $this->Group->groupBy($this->query->identifier($columns));

        return $this;
    }

    /**
     * @param $column
     * @param null   $operator
     * @param null   $value
     * @param string $chainType
     *
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $chainType = 'AND') {
        $this->values[] = $value;

        $this->Having->having($this->query->identifier($column), $operator, $chainType);

        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function orHaving($column, $operator = null, $value = null) {
        $this->values[] = $value;

        $this->Having->orHaving($this->query->identifier($column), $operator);

        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function havingCount($column, $operator = null, $value = null) {
        $this->values[] = $value;

        $this->Having->havingCount($this->query->identifier($column), $operator);

        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function havingMax($column, $operator = null, $value = null) {
        $this->values[] = $value;

        $this->Having->havingMax($this->query->identifier($column), $operator);

        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function havingMin($column, $operator = null, $value = null) {
        $this->values[] = $value;

        $this->Having->havingMin($this->query->identifier($column), $operator);

        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function havingAvg($column, $operator = null, $value = null) {
        $this->values[] = $value;

        $this->Having->havingAvg($this->query->identifier($column), $operator);

        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     *
     * @return $this
     */
    public function havingSum($column, $operator = null, $value = null) {
        $this->values[] = $value;

        $this->Having->havingSum($this->query->identifier($column), $operator);

        return $this;
    }

    /**
     * @param $number
     *
     * @return $this
     */
    public function offset($number) {
        $this->Offset->offset($number);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        if (empty($this->table)) {
            trigger_error('No table is set for selection', E_USER_ERROR);
        }

        $sql = $this->getSelect() . ' ' . $this->getColumns();
        $sql .= ' FROM ' . $this->table;
        $sql .= $this->Join;
        $sql .= $this->Where;
        $sql .= $this->Group;
        $sql .= $this->Having;
        $sql .= $this->Order;
        $sql .= $this->Limit;
        $sql .= $this->Offset;
        return $sql;
    }

    /**
     * @return string
     */
    protected function getSelect() {
        if ($this->distinct) {
            return 'SELECT DISTINCT';
        }

        return 'SELECT';
    }

    /**
     * @return string
     */
    protected function getColumns() {
        return $this->query->identifier(implode(' , ', $this->columns ?: ['*']));
    }

    /**
     * @param $distinct
     *
     * @return string
     */
    protected function setDistinct($distinct) {
        if ($distinct) {
            return 'COUNT( DISTINCT';
        }

        return 'COUNT(';
    }

    /**
     * @param $as
     *
     * @return string
     */
    protected function setAs($as) {
        if (empty($as)) {
            return '';
        }

        return ' AS ' . $this->query->identifier($as);
    }

    /**
     *  执行一条预处理语句
     * @return \PDOStatement
     */
    public function execute() {
        $sql = $this;
        return $this->query->query($sql, $this->values);
    }

}
