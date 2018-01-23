<?php

namespace YoPHP\ORM\Statement;

/**
 * Class DeleteStatement
 */
class Delete extends Statement {

   

    /**
     * @return string
     */
    public function __toString() {
        if (empty($this->table)) {
            trigger_error('No table is set for deletion', E_USER_ERROR);
        }

        $sql = 'DELETE FROM ' . $this->table;
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

}
