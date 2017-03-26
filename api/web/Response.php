<?php

class Response {

    public $content;
    public $headers;
    public $statusCode;
    public $contentType;

    function __construct($content, $statusCode = 200) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->contentType = 'text/html';
    }

    function render() {
        http_response_code($this->statusCode);
        header("Content-Type: $this->contentType");
        echo $this->content;
    }

}
