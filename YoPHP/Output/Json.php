<?php

namespace YoPHP\Output;

use YoPHP\Output;
use Exception;

class Json {

    /** @var string */
    protected $contentType = 'application/json';

    /**
     * @inheritdoc
     */
    public function prepare() {
        Output::setContentType($this->contentType);


        $content = Output::getContent();
        if (!is_string($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);

            Output::setContent($content);
        } elseif (!$this->isJsonString($content)) {
            throw new Exception("Json encode error: '" . json_last_error_msg() . "'");
        }
    }

    /**
     * @param string $data
     * @return bool
     */
    protected function isJsonString($data) {
        if (!is_string($data) && !is_null($data)) {
            return false;
        }
        json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        return true;
    }

}
