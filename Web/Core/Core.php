<?php

include_once("Notify.class.php");

/**
 * 
 * @param MvcRoute[] $routes
 * @throws Exception
 */
function executeRouting($routes) {
    //thanks to https://github.com/panique/php-mvc for some of the url techniques
    $dir = dirname(__FILE__);
    include($dir . '/Controller.class.php');

    //grab the url
    $url = (isset($_GET['url'])) ? $_GET['url'] : '';
    $url = rtrim($url, '/');
    $url = filter_var($url, FILTER_SANITIZE_URL);
    $urlParts = explode('/', $url);

    $matchedRoute = null;
    //find the first route that matches
    foreach ($routes as $route) {
        if ($route->match($url) === true) {
            $matchedRoute = $route;
            break;
        }
    }
    if ($matchedRoute == null) {
        throw new Exception("No matching route for '$url' found.");
    }

    //get the route items
    $routeItems = $route->getSegments($url);

    $controllerName = $routeItems["controller"];
    $actionName = $routeItems["action"];

    $controllerClassName = $controllerName . 'Controller';
    $controllerPath = $dir . '/../Controllers/' . $controllerClassName . '.php';
    //does this controller exist?
    if (file_exists($controllerPath)) {
        include($controllerPath);
        $controller = new $controllerClassName;
        //see if action exists
        if (method_exists($controller, $actionName)) {
            $args = getActionArgs($controllerClassName, $actionName, $routeItems);
            $controller = new $controller();
            //execute the controller action
            call_user_func_array(array($controller, $actionName), $args);
            //$controller->{$actionName}();
        } else {
            throw new Exception("No action '$actionName' found in controller '$controllerName'");
        }
    }
}

/**
 * Checks out the method in the class provided and tries to bind the named GET or POST 
 * variables, along with the url action items, with the parameters of the method. 
 */
function getActionArgs($className, $methodName, $routeItems) {
    //get the parameter names for the action
    $functionParamNames = [];
    $functionDefaults = [];
    $rc = new ReflectionClass($className);
    $ctor = $rc->getMethod($methodName);
    foreach ($ctor->getParameters() as $param) {
        $cn = $param->getClass();
        if ($cn instanceof ReflectionClass) {
            $cn = $cn->getName();
        }
        $name = $param->getName();
        $functionParamNames[] = $name;
        $functionDefaults[$name] = ($param->isDefaultValueAvailable()) ? $param->getDefaultValue() : null;
    }
    //determine whether POST or GET parameters should be used
    $paramList = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;

    $args = [];
    foreach ($functionParamNames as $name) {
        //check the routeItems first
        if (isset($routeItems[$name])) {
            $args[] = $routeItems[$name];
        }
        //if this parameter is not in the route items, check the param list
        else {
            $args[] = (isset($paramList[$name])) ? $paramList[$name] : $functionDefaults[$name];
        }
    }
    return $args;
}

class MvcRoute {

    public $routeName;
    public $url;
    public $defaults;
    private $segments;

    /**
     * 
     * @param string $routeName
     * @param string $url - the route url, in the format  "{controller}/{action}/{id}". Each item noted by {}.
     * @param array $defaults - the array corresponding with the variables found in the above url
     */
    function __construct($routeName, $url, $defaults = []) {
        $this->routeName = $routeName;
        $this->url = $url;
        $this->defaults = $defaults;
        $this->segments = MvcRoute::ParseUrl($url);
        //We must have a controller in either defaults or segments
        if (isset($this->defaults["controller"]) == false && isset($this->segments["{controller}"]) == false) {
            throw new Exception("No controller specified in this route '$this->routeName'. A controller must be specified in either the route url or the defaults. ");
        }
        //We must have a controller in either defaults or segments
        if (isset($this->defaults["action"]) == false && isset($this->segments["{action}"]) == false) {
            throw new Exception("No action specified in this route '$this->routeName'. An action must be specified in either the route url or the defaults.");
        }
    }

    private function getDefaultValue($key) {
        //remove the braces from around the key
        $myKey = $this->getVariableSegmentName($key);
        $val = (isset($this->defaults[$myKey])) ? $this->defaults[$myKey] : null;
        return $val;
    }

    private function getVariableSegmentName($key) {
        $myKey = str_replace('{', '', $key);
        $myKey = str_replace('}', '', $myKey);
        return $myKey;
    }

    /**
     * Determines if the string passed in is wrapped in brackets {}
     * @param string $str
     */
    public static function SegmentIsVariable($str) {
        if (strpos($str, "{") === 0 && strpos($str, '}') == strlen($str) - 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function PrepareUrl($urlParam) {
        $url = $urlParam;
        //rip off the querystring 
        $qIdx = strpos($url, '?');
        if ($qIdx !== false) {
            $url = substr($url, 0, $qIdx);
        }
        //remove any trailing slash
        if (substr($url, -1) == '/') {
            $url = substr(url, 0, -1);
        }
        return $url;
    }

    /**
     * Determines if this route matches the url. 
     * @param string $urlParam - the url to compare to this route
     * @return boolean - true if this route matches, false if it does not
     */
    function match($urlParam) {
        $url = MvcRoute::PrepareUrl($urlParam);
        //now split the url by slashes. we will have one entry per url part
        $urlSegments = explode('/', $url);
        //remove empty items
        $urlSegments = array_filter($urlSegments);
        $i = 0;
        foreach ($this->segments as $mySegment) {
            $urlSegment = (isset($urlSegments[$i])) ? $urlSegments[$i++] : null;
            //if mySegment is NOT a variable
            if (MvcRoute::SegmentIsVariable($mySegment) === false) {
                //if the url segment does not match my segment, this route does not match up
                if ($mySegment !== $urlSegment) {
                    return false;
                }
            }
            //segment is a variable
            else {
                //see if there is value in the urlSegment
                if ($urlSegment === null) {
                    //there is no val in the urlSegment. do we have a default value for this segment?
                    if ($this->getDefaultValue($mySegment) === null) {
                        return false;
                    } else {
                        //continue, we have a default value for this segment so all is good
                    }
                } else {
                    //the urlSegment has a value, so this is all good.
                }
            }
        }
        //there should be no more urlSegments. if there are, this route doesn't map
        if (isset($urlSegments[$i])) {
            return false;
        }
        return true;
    }

    /**
     * parses through the url and extracts all segment values, either from the url or from the defaults
     * @param type $urlParam
     */
    public function getSegments($urlParam) {
        $finalSegmentValues = [];
        $url = MvcRoute::PrepareUrl($urlParam);
        //now split the url by slashes. we will have one entry per url part
        $urlSegments = explode('/', $url);
        //remove empty items
        $urlSegments = array_filter($urlSegments);
        $i = 0;
        foreach ($this->segments as $mySegment) {
            $key = $this->getVariableSegmentName($mySegment);
            $urlSegment = (isset($urlSegments[$i])) ? $urlSegments[$i++] : null;
            //if mySegment is NOT a variable
            if (MvcRoute::SegmentIsVariable($mySegment) === false) {
                //if the url segment does not match my segment, this route does not match up
                if ($mySegment !== $urlSegment) {
                    return false;
                } else {
                    
                }
            }
            //segment is a variable
            else {
                //if the url segment has no value, use the default
                if ($urlSegment === null) {
                    $finalSegmentValues[$key] = $this->getDefaultValue($mySegment);
                }
                //the url segment has a value, so use it
                else {
                    $finalSegmentValues[$key] = $urlSegment;
                }
            }
        }
        return $finalSegmentValues;
    }

    static function ParseUrl($url) {
        $urlPieces = [];
        $pieces = explode("/", $url);
        foreach ($pieces as $piece) {
            //$variableName = str_replace('{', '', $piece);
            // $variableName = str_replace('}', '', $variableName);
            $urlPieces[$piece] = $piece;
        }
        return $urlPieces;
    }

}
