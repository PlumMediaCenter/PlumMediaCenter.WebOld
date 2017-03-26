<?php

class JsonResponse extends Response {

    function __construct($content, $statusCode = 200) {
        parent::__construct(null, $statusCode);
        $this->content = json_encode($content);
        $this->contentType = 'application/json';
    }

}
