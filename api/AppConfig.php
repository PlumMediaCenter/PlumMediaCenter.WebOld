<?php

class AppConfig {

    /**
     * 
     * @return string
     */
    public static function GetSendgridApiKey() {
        return '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';
    }

    /**
     * 
     * @return string
     */
    public static function GetFromEmailAddress() {
        return 'notifications@bronley.com';
    }

    /**
     * Get the base path, excluding the protocol and port
     */
    public static function GetBasePath() {
        $parts = AppConfig::GetUrlParts();
        $parts = (object) $parts;
        $baseUrl = AppConfig::GetBaseUrl();
        $root = "$parts->scheme://$parts->host:$parts->port/";
        $basePath = '/' . str_replace($root, '', $baseUrl);
        return $basePath;
    }

    private static function GetUrlParts() {
        if (isset($_SERVER['REQUEST_URI'])) {
            $parts = parse_url(
                    (isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off') ? 'https://' : 'http://') .
                    (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '')) . $_SERVER['REQUEST_URI']
            );
            $parts['port'] = $_SERVER["SERVER_PORT"]; // Setup protocol for sure (80 is default)
            return $parts;
        }
    }

    private static $baseUrl = null;

    /**
     * Get the full url to the root of this app. This url points to the full url that contains the index.php file
     * @return string
     */
    public static function GetBaseUrl() {
        if (AppConfig::$baseUrl == null) {

            $rootFolder = dirname(__DIR__);
            //get the name of this folder
            $thisDirectoryName = pathinfo($rootFolder, PATHINFO_FILENAME);
            $lowerDirectoryName = strtolower($thisDirectoryName);
            $url = AppConfig::GetFullUrl();
            //handle the case where the api folder name is the name of the 
//        if ($lowerDirectoryName == 'api') {
//            $thisDirectoryName = "api/$thisDirectoryName";
//        }
            $baseUrl = substr($url, 0, strpos(strtolower($url), strtolower("/$thisDirectoryName/")));
            $baseUrl = $baseUrl . "/$thisDirectoryName/";
            AppConfig::$baseUrl = $baseUrl;
        }
        return AppConfig::$baseUrl;
    }

    public static function GetLoginUrl() {
        return AppConfig::GetBaseUrl();
    }

    public static function GetCreateEventUrl() {
        return AppConfig::GetBaseUrl() . '#/create-event';
    }

    public static function GetCreateEventSeriesUrl() {
        return AppConfig::GetBaseUrl() . '#/create-event-series';
    }

    /**
     * Get the full url
     * @return type
     */
    public static function GetFullUrl() {
        if (isset($_SERVER['REQUEST_URI'])) {
            $parts = AppConfig::GetUrlParts();
            return http_build_url('', $parts);
        }
    }

    /**
     * Gets the relative path...only the path AFTER the base url
     * @return type
     */
    public static function GetRelativeUrl() {
        $baseUrl = AppConfig::GetBaseUrl();
        $fullUrl = AppConfig::GetFullUrl();
        //remove the base url from the full url. Everything else is the relative url
        return '/' . str_replace($baseUrl, '', $fullUrl);
    }

    /**
     * 
     * @return string
     */
    public static function GetJwtKey() {
        return '7B6092E7BA3174B9265C6B4B69A2D5F3BC1A16EDAFCEDE3C1C30D2B53D94F9D4';
    }

}
