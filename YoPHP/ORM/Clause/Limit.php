<?php

namespace YoPHP\ORM\Clause;

/**
 * Class Limit
 */
class Limit extends Container {

    /**
     * @var null
     */
    private $limit = null;

    /**
     * @param int $number
     * @param int $offset
     */
    public function limit($number, $offset = 0, $page = null) {

        if (preg_match('/^[1-9][0-9]*$/i', $page)) {
            $offset = intval($number) * (intval($page) - 1);
        }

        if (preg_match('/^[1-9][0-9]*$/i', $offset) && preg_match('/^[1-9][0-9]*$/i', $number)) {
            $this->limit = intval($number) . ' OFFSET ' . intval($offset);
        } elseif (preg_match('/^[1-9][0-9]*$/i', $number)) {
            $this->limit = intval($number);
        }
    }

    /**
     * @return string
     */
    public function __toString() {
        if (is_null($this->limit)) {
            return '';
        }
        $limit = $this->limit;
        $this->limit = null;
        return ' LIMIT ' . $limit;
    }

}
