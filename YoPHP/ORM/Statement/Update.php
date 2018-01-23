<?php

namespace YoPHP\ORM\Statement;

/**
 * Class UpdateStatement
 */
class Update extends Statement {
    /**
     * Constructor.
     *
     * @param Database $dbh
     * @param array    $pairs
     * @param string    $table
     */
//    public function __construct($dbh, array $pairs, $table) {
//        parent::__construct($dbh);
//        !empty($table) && $this->table($table);
//        $this->set($pairs);
//    }

    /**
     * @param array $pairs
     *
     * @return $this
     */
    public function set(array $pairs) {
        foreach ($pairs as $column => $value) {
            if (is_array($value) && count($value) === 2) {
                if (preg_match('/^(\+|\-|\*|\/)$/', $value[0]) && is_numeric($value[1])) {
                    $this->columns[] = $this->query->identifier($column) . ' = ' . $this->query->identifier($column) . ' ' . $value[0] . ' ?';
                    $this->values[] = $value[1];
                    continue;
                }
            }
            $this->columns[] = $this->query->identifier($column) . ' = ?';
            $this->values[] = is_array($value) ? json_encode($value) : $value;
        }
//        print_r($this->columns);
//        print_r($this->values);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        if (empty($this->table)) {
            trigger_error('No table is set for update', E_USER_ERROR);
        }
        if (empty($this->columns) && empty($this->values)) {
            trigger_error('Missing columns and values for update', E_USER_ERROR);
        }
        $sql = 'UPDATE ' . $this->table;
        $sql .= ' SET ' . $this->getColumns();
        $sql .= $this->Where;
        $sql .= $this->Order;
        $sql .= $this->Limit;
        return $sql;
    }

    /**
     * @return int
     */
    public function execute() {
        $sql = $this;
        return $this->query->exec($sql, $this->values);
    }

    /**
     * @return string
     */
    protected function getColumns() {
        return implode(' , ', $this->columns);
    }

}
