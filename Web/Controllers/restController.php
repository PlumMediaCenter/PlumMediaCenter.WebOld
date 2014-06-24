<?php

require_once(dirname(__FILE__) . '/../Models/rest/IndexModel.php');

class restController extends Controller {

    /**
     * Default action
     */
    function Index() {
        $model = new \Models\rest\IndexModel();
        return $this->handleOutput($model);
    }

    private function handleOutput($model) {
        $outputFormat = $this->getOutputFormat();
        switch ($outputFormat) {
            case OutputFormat::json:
                return json($model);
                break;
            case OutputFormat::html:
                return view($model, "rest/Index");
                break;
        }
    }

    /**
     * Get the format of the output. If non or invalid was specified, return default value
     * @return OutputFormat
     */
    private function getOutputFormat() {
        $requestFormat = OutputFormat::html;

        if (isset($_REQUEST["f"]) === true) {
            $requestFormat = strtolower($_REQUEST["f"]);
        }
        switch ($requestFormat) {
            case OutputFormat::json:
                $requestFormat = OutputFormat::json;
                break;
            default:
                $requestFormat = OutputFormat::html;
                break;
        }
        return $requestFormat;
    }

}

class OutputFormat {

    const json = "json";
    const html = "html";

}
