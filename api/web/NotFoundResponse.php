<?php

class NotFoundResponse extends Response {

    function __construct($message = 'Resource not found') {
        parent::__construct(null, 404);
        try {
            throw new Exception($message);
        } catch (Exception $e) {
            $responseError = new ResponseError($e);
            $this->content = json_encode($responseError);
        }
        $this->contentType = 'application/json';
    }

}
