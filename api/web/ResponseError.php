<?php

class ResponseError {

    public $message;
    public $file;
    public $line;
    public $previous;
    public $trace;
    public $traceString;

    function __construct(Exception $e = null) {
        if ($e) {
            $this->message = $e->getMessage();
            $this->file = $e->getFile();
            $this->line = $e->getLine();
            $previous = $e->getPrevious();
            if ($previous) {
                $this->previous = new ResponseError($e->getPrevious());
            }
            $this->trace = $e->getTrace();
            $this->traceString = $e->getTraceAsString();
        }
    }

}
