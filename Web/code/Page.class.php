<?php

include_once("code/Security.class.php");
include_once("code/functions.php");
include_once("code/Model.class.php");

class Page {

    private $file;
    public $modelPath;
    public $markupPath;
    public $model;
    private $modelIsImported = false;

    function __construct($file) {
        $this->file = $file;
        $this->markupPath = str_replace(".php", ".markup.php", $file);
        $this->modelPath = str_replace(".php", ".model.php", $file);

        //load all of the public variables declared 
        //extract($this);
    }

    function getModel() {
        //if the model was already created, return it
        if ($this->model != null) {
            return $this->model;
        }
        if (file_exists($this->modelPath)) {
            //if the model has not been imported yet, import it and instantiate an instance of the model.
            if ($this->modelIsImported == false) {
                $this->modelIsImported = true;

                include($this->modelPath);
                $modelName = str_replace(".model", "", pathinfo($this->modelPath, PATHINFO_FILENAME)) . "Model";
                if (class_exists($modelName)) {
                    $this->model = new $modelName();
                } else {
                    $this->model = (object) [];
                }
            }
        } else {
            $this->model = (object) [];
        }
        return $this->model;
    }

    function setModel($model = null) {
        if ($model == null) {
            $this->model = $this->getModel();
        } else {
            $this->model = $model;
        }
    }

    function show($layout = "layout.php", $content = null) {
        extract((array) $this->getModel());
        //if the title is not set, set it
        if (isset($title) == false) {
            $title = "Plum Video Player";
        }
        ob_start();
        //if the content variable is included, use that instead of a markup page
        if ($content != null) {
            echo $content;
        } else {
            if (file_exists($this->markupPath)) {
                //load the markup
                include($this->markupPath);
            }
        }
        global $body;
        $body = ob_get_contents();
        ob_end_clean();
        //load the layout, which will load the markup into the layout
        include($layout);
    }

}

?>
