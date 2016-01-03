<?php

class CompositeException extends Exception {

    public $exceptions = [];
    public $errorMessage;
    function __construct($exceptions) {
        $this->unrollException($exceptions);
        $this->errorMessage = 'Multiple exceptions were encountered';
    }

    function unrollException($exceptions) {
        //look at each exception. if any of them are composite exceptions, unroll them

        foreach ($exceptions as $exception) {
            if (isset($exception->exceptions)) {
                $this->unrollException($exception->exceptions);
            } else {
                $this->exceptions = $exception;
            }
        }
    }
}
