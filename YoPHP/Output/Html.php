<?php

namespace YoPHP\Output;

use YoPHP\Output;
use Exception;

class Html {

    /** @var string */
    protected $contentType = 'text/html';

    /**
     * @inheritdoc
     */
    public function prepare() {
        Output::setContentType($this->contentType);

        if (!is_string(Output::getContent())) {
            throw new Exception('只能使用字符串或空内容');
        }
    }

}
