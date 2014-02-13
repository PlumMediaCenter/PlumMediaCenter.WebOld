<?php

global $sections;
global $currentSectionName;
$sections = [];

$currentSectionName = null;

/**
 * This is the function that actually performs the rendering. It loads the view, the layout, and combines them
 * @param object $model - the model for the view. only the second parameter if a viewString is provided
 * @param string $viewString - the view to display. Must be the relative path from within the views folder. I.e. /Home/Index 
 * 
 */
function view($theModel = null, $viewString = null) {
    global $sections;
    global $model;
    //save the model to the global variable scope as the model for this view
    $model = $theModel;
    //get the action name and controller name of the action that called this function
    $callers = debug_backtrace();
    $actionName = $callers[1]['function'];
    $controllerName = str_replace('Controller', '', $callers[1]["class"]);

    //if no view information was provided, retrieve the controller/action that called View and look for its corresponding view
    if ($viewString == null) {
        $viewString = "$controllerName/$actionName";
    } else {
        //if the viewString does not have a slash in it, we need to get the controller name.
        if (strpos($viewString, "/") === false) {
            $viewString = "$controllerName/$viewString";
        }
    }
    $root = dirname(__FILE__) . '/..';
    $viewString = ($viewString == null) ? "Home/Index" : $viewString;
    $viewPath = "$root/Views/$viewString.php";
    if (!file_exists($viewPath)) {
        throw new Exception("Unable to find view at path '$viewPath'");
    }
    //include the _Viewstart file at the beginning of the return of every view
    include("$root/Views/_Viewstart.php");
    //get the layout option 
    $layout = (isset($layout)) ? $layout : null;
    //include the view
    ob_start();
    include($viewPath);
    $sections["body"] = ob_get_contents();
    ob_end_clean();
    //layout should have been included in the _Viewstart.php or the view page. 
    if ($layout != null) {
        include("$root/Views/$layout");
    }
}

/**
 * To be used by views to organize items in the view into sections that can be printed
 * to the layout page
 * @param string $sectionName - the name of the section
 */
function section($sectionName) {
    global $currentSectionName;
    $currentSectionName = $sectionName;
    ob_start();
}

/**
 * Ends the current section
 */
function endSection() {
    global $sections;
    global $currentSectionName;
    $sectionData = ob_get_contents();
    ob_end_clean();
    if ($currentSectionName != null) {
        $sections[$currentSectionName] = $sectionData;
    }
}

/**
 * Prints the named section to the output
 * @global array $sections
 * @param string $sectionName - the name of the section
 * @param boolean $isOptional - indicates if the section is optional or not. 
 * @throws Exception - if isOptional = false and no section was found, throw exception
 */
function renderSection($sectionName, $isOptional = true) {
    global $sections;
    if (isset($sections[$sectionName])) {
        echo $sections[$sectionName];
    } else {
        if ($isOptional === false) {
            throw new Exception("No section with name '$sectionName' found.");
        }
    }
}

global $bUrl;
$bUrl = null;

function baseUrl() {
    global $bUrl;
    if ($bUrl == null) {
        $s = $_SERVER;
        $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];

        $scriptName = $s["SCRIPT_NAME"];
        $baseUrl = str_replace("/index.php", "", $scriptName);
        $bUrl = $protocol . '://' . $host . $baseUrl;
    }
    return $bUrl;
}

global $bPath;
$bPath = null;

/**
 * Returns the base file path to the root of this application. For example, assuming your root 
 * web directory is /var/www or C:\apache\www\, www.website.com/myapp would have a base path of
 * /var/www/myapp or C:\apache\www\myapp
 */
function basePath() {
    global $bPath;
    if ($bPath == null) {
        $fullPathToIndex = $_SERVER["SCRIPT_FILENAME"];
        $bPath = dirname($fullPathToIndex);
    }
    return $bPath;
}

function urlContent($contentUrl) {
    //if the contentUrl contains ~, then replace that with the base web url
    if (strpos($contentUrl, "~") !== false) {
        $contentUrl = str_replace("~/", "", $contentUrl);
        $bUrl = baseUrl();
        $newUrl = "$bUrl/$contentUrl";
    } else {
        $newUrl = $contentUrl;
    }
    echo $newUrl;
}

/**
 * Converts a controller/action into a full url. 
 * @param string $actionString - in the format of either "Action" or "Controller/Action". If controller is omitted,
 *                                  then the calling controller will be used
 * @param string $controllerName - the name of the controller. If omitted, the calling controller will be assumed
 * @param array $parameters - a list of GET parameters to include in the url
 */
function urlAction($actionString, $parameters = []) {
    //parse the action string. 
    $actionParts = explode("/", $actionString);
    //if there are two parts, both the controller AND the action were provided
    if (count($actionParts) === 2) {
        $controllerName = $actionParts[0];
        $actionName = $actionParts[1];
    }
    //if there is only one part, it is the action. 
    else if (count($actionParts) === 1) {
        $controllerName = null;
        $actionName = $actionParts[0];
    } else {
        $actionName = null;
        $controllerName = null;
    }
    //if the controller name is null, try to figure out which controller to use
    if ($controllerName == null) {
        $callers = debug_backtrace();
        //this is the view that called urlAction
        $callerPath = $callers[2]['file'];
        //replace any windows slashes with unix ones
        $callerPath = str_replace('\\', '/', $callerPath);
        $basePath = basePath();
        $relativeCallerPath = str_replace($basePath . '/', '', $callerPath);
        //split the remainder of the path by slashes and the second item is our controller (first is Views/)
        $parts = explode("/", $relativeCallerPath);
        $controllerFilename = $parts[1];
        $controllerName = str_replace('Controller.php', '', $controllerFilename);
    }

    //at this point, we can safely assume that we have a controller name and an action name
    //see if the action exists. if it doesn't, throw an exception
    if (actionExists($controllerName, $actionName) != true) {
        throw new Exception("No action with name '$actionName' could be found in controler '$controllerName'");
    }

    //create the querystring parameters string, if any values were provided
    $parameterString = "";
    $amp = "";
    foreach ($parameters as $key => $val) {
        $parameterString .= $amp . "$key=$val";
        $amp = "&";
    }

    //prepend the question mark if any parameters were present
    if (strlen($parameterString) > 0) {
        $parameterString = "?$parameterString";
    }
    $url = baseUrl() . "/$controllerName/$actionName" . $parameterString;
    echo $url;
}

/**
 * Determines if an action exists
 * @param string $controllerName - the name of the controller that the action resides in
 * @param string $actionName - the name of the action to verify existence of
 * @return boolean - true if the action exists, false if it does not
 */
function actionExists($controllerName = null, $actionName = null) {
    //both parameters must be provided. otherwise, we can't determine the action
    if ($controllerName === null || $actionName === null) {
        return false;
    }
    $controllerClassName = $controllerName . 'Controller';
    $controllerPath = dirname(__FILE__) . '/../Controllers/' . $controllerClassName . '.php';
    //does this controller exist?
    if (file_exists($controllerPath)) {
        include_once($controllerPath);
        //see if action exists
        if (method_exists($controllerClassName, $actionName)) {
            return true;
        }
    }
    return false;
}

/**
 * Renders a partial view to the screen.
 * @param string $viewPath - the path to the view, relative to the app. Use "~/" to indicate the base of the application
 * @param object $viewModel - the model to pass to the view
 */
function partial($viewPath, $viewModel = null) {
    global $model;

    $relativeViewPath = str_replace("~/", "", $viewPath);
    $fullViewPath = basePath() . '/' . $relativeViewPath;
    if (file_exists($fullViewPath) === true) {
        //store the parent model
        $parentViewModel = $model;
        //replace the global model with this view's model IF a model was specified. othwerwise, use existing model
        $model = ($viewModel == null) ? $parentViewModel : $viewModel;
        //draw the partial view
        include($fullViewPath);
        //reinstate the parent model
        $model = $parentViewModel;
    } else {
        throw new Exception("Unable to find view '$viewpath'");
    }
}

/**
 * A wrapper function for writing out json to the output 
 * @param type $object
 */
function json($object) {
    echo json_encode($object, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
}
