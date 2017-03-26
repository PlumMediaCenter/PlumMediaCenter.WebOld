<?php

class ApiController {

    private $bodyString;

    public function getBody() {
        if ($this->bodyString === null) {
            $this->bodyString = file_get_contents('php://input');
        }
        return json_decode($this->bodyString);
    }

}
