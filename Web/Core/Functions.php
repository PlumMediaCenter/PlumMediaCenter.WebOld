<?php

global $sections;
global $currentSectionName;
global $baseUrl;
global $basePath;
$sections = [];
$currentSectionName = null;
$baseUrl = null;
$basePath = null;

/**
 * This is the function that actually performs the rendering. It loads the view, the layout, and combines them
 * @param object $model - the model for the view. only the second parameter if a viewString is provided
 * @param string $viewString - the view to display. Must be the relative path from within the views folder. I.e. /Home/Index 
 * 
 */
function view($theModel = null, $routeString = null) {
    global $sections;
    global $model;
    //save the model to the global variable scope as the model for this view
    $model = $theModel;

    $routeString = _getRoute($routeString);

    $root = dirname(__FILE__) . '/..';

    $viewPath = "$root/Views/$routeString.php";
    if (!file_exists($viewPath)) {
        throw new Exception("Unable to find view at path '$viewPath'");
    }
    if (file_exists("$root/Views/_Viewstart.php")) {
        //include the _Viewstart file at the beginning of the return of every view
        include("$root/Views/_Viewstart.php");
    }
    //get the layout option 
    $layout = (isset($layout)) ? $layout : null;
    //include the view
    ob_start();
    include($viewPath);
    $sections['body'] = ob_get_contents();
    ob_end_clean();
    //layout should have been included in the _Viewstart.php or the view page. 
    if ($layout != null) {
        include("$root/Views/$layout");
    }else{
        //if the layout is null, write the body immediately
        echo $sections['body'];
    }
}

/**
 * Redirects the page to the specified action
 * @param string $routeString
 * @param type $routeValues
 */
function RedirectToAction($routeString, $routeValues = null) {
    $routeString = _getRoute($routeString);
    $redirectUrl = getUrlAction($routeString, $routeValues);
    header("Location: $redirectUrl", true, 301);
    exit;
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

function baseUrl() {
    global $baseUrl;
    if ($baseUrl == null) {
        $s = $_SERVER;
        $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];

        $scriptName = $s['SCRIPT_NAME'];
        $baseUrl = str_replace('/index.php', '', $scriptName);
        $baseUrl = $protocol . '://' . $host . $baseUrl;
    }
    return $baseUrl;
}

/**
 * Returns the base file path to the root of this application. For example, assuming your root 
 * web directory is /var/www or C:\apache\www\, www.website.com/myapp would have a base path of
 * /var/www/myapp or C:\apache\www\myapp
 */
function basePath() {
    global $basePath;
    if ($basePath == null) {
        $fullPathToIndex = $_SERVER['SCRIPT_FILENAME'];
        $basePath = dirname($fullPathToIndex);
    }
    return $basePath;
}

/**
 * Echoes the result of getUrlContent
 * @param type $contentUrl
 */
function urlContent($contentUrl) {
    echo GetUrlContent($contentUrl);
}

/**
 * Converts the contentUrl to a fully qualified url
 * @param string $contentUrl
 * @return string
 */
function getUrlContent($contentUrl) {
    //if the contentUrl contains ~, then replace that with the base web url
    if (strpos($contentUrl, '~') !== false) {
        $contentUrl = str_replace('~/', '', $contentUrl);
        $bUrl = baseUrl();
        $newUrl = "$bUrl/$contentUrl";
    } else {
        $newUrl = $contentUrl;
    }
    return $newUrl;
}

/**
 * Converts a controller/action into a full url. Echoes that result
 * @param string $actionString - in the format of either "Action" or "Controller/Action". If controller is omitted,
 *                                  then the calling controller will be used
 * @param string $controllerName - the name of the controller. If omitted, the calling controller will be assumed
 * @param array $parameters - a list of GET parameters to include in the url
 */
function urlAction($actionString, $parameters = []) {
    echo getUrlAction($actionString, $parameters);
}

/**
 * Converts a controller/action into a full url. 
 * @param string $actionString - in the format of either "Action" or "Controller/Action". If controller is omitted,
 *                                  then the calling controller will be used
 * @param string $controllerName - the name of the controller. If omitted, the calling controller will be assumed
 * @param array $parameters - a list of GET parameters to include in the url
 */
function getUrlAction($actionString, $parameters = []) {
    //if the parameters item is null, set it to an empty array
    $parameters = ($parameters == null)? []: $parameters;
    //parse the action string. 
    $actionParts = explode('/', $actionString);
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
        $controllerName = _controllerFromBacktrace();
    }

    //at this point, we can safely assume that we have a controller name and an action name
    //see if the action exists. if it doesn't, throw an exception
    if (actionExists($controllerName, $actionName) != true) {
        throw new Exception("No action with name '$actionName' could be found in controler '$controllerName'");
    }

    //create the querystring parameters string, if any values were provided
    $parameterString = '';
    $amp = '';
    foreach ($parameters as $key => $val) {
        $parameterString .= $amp . "$key=$val";
        $amp = '&';
    }

    //prepend the question mark if any parameters were present
    if (strlen($parameterString) > 0) {
        $parameterString = "?$parameterString";
    }
    $url = baseUrl() . "/$controllerName/$actionName" . $parameterString;
    return $url;
}

/**
 * Returns the controller name from the backtrace
 * @return string - the controller name that is being executed
 */
function _controllerFromBacktrace() {
    $route = _routeFromBacktrace();
    $parts = explode('/', $route);
    return $parts[0];
}

/**
 * Analyzes the routeString provided and generates a Controller/Action string based on the values provided
 * and suppliments with any backtrace route available
 * @param type $routeString
 */
function _getRoute($routeString) {
    $returnRouteString = null;
    //if no view information was provided, retrieve the controller/action that called View and look for its corresponding view
    if ($routeString == null) {
        //get the action name and controller name of the action that called this function
        $returnRouteString = _routeFromBacktrace();
    } else {
        //if the viewString does not have a slash in it, we need to get the controller name.
        if (strpos($routeString, '/') === false) {
            $controllerName = _controllerFromBacktrace();
            $returnRouteString = "$controllerName/$routeString";
        } else {
            $returnRouteString = $routeString;
        }
    }
    return $returnRouteString;
}

/**
 * Investigates the backtrace until a controller and action can be found. 
 * @throws Exception
 */
function _routeFromBacktrace() {
    $controllerName = null;
    $actionName = null;

    $callers = debug_backtrace();
    $callerCount = count($callers);
    //spin through the list of callers and find the first one that ends with Controller.php
    for ($i = 0; $i < $callerCount; $i++) {
        $currentCaller = $callers[$i];
        //if the currentCaller is NOT a class
        if (isset($currentCaller['file']) == true) {

            $path = $currentCaller['file'];
            $pos = strrpos($path, 'Controller.php');
            //if $callerPath ends in Controller.php, then this is the controller file. Now see if 
            if ($pos === strlen($path) - 14) {
                $controllerName = _controllerNameFromPath($path);
                $controllerClassName = $controllerName . 'Controller';
                //if the function in this caller is an action, this is the caller we are looking for
                $potentialActionName = $currentCaller['function'];

                //if the discovered controller has a method with the discovered action name, this is
                //the controller and action we are looking for
                if (method_exists($controllerClassName, $potentialActionName)) {
                    $controllerName = _controllerNameFromPath($currentCaller['file']);
                    $actionName = $currentCaller['function'];
                    break;
                }
            }
        } else {
            $className = $currentCaller['class'];
            //the current caller is a class.
            //if the current caller class name ends in "Controller"
            $pos = strrpos($className, 'Controller');
            //if $callerPath ends in 'Controller', then this is the controller file. Now see if 
            if ($pos === strlen($className) - 10) {
                $controllerName = substr($className, 0, $pos);
                $actionName = $currentCaller['function'];
            }
        }
    }

    if ($controllerName === null) {
        throw new Exception("Unable to determine active controller.");
    }
    return "$controllerName/$actionName";
}

/**
 * Extracts the controller name from a full path
 * @param string $path - the full path to use to extract the controller name from
 * @return string - the extracted controller name 
 * @throws Exception
 */
function _controllerNameFromPath($path) {
    //replace any windows slashes with unix ones
    $cleansedPath = str_replace('\\', '/', $path);
    $basePath = basePath();
    $relativePath = str_replace($basePath . '/', '', $cleansedPath);
    //split the remainder of the path by slashes and the second item is our controller (first is Views/)
    $parts = explode("/", $relativePath);
    $controllerFilename = $parts[1];
    $count = null;
    $controllerName = str_replace('Controller.php', '', $controllerFilename, $count);
    if ($count === 0) {
        throw new Exception("Unable to extract a controller name from '$path'");
    } else {
        return $controllerName;
    }
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

    $relativeViewPath = str_replace('~/', '', $viewPath);
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
