<?php

function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

//declare a custom error handler so we can try catch warnings 
set_error_handler('handleError');

function getLibrary() {
    $lib = null;
    if (file_exists("videos.json") === true) {
        //load the json file into memory
        $json = $string = file_get_contents("videos.json");
        $lib = json_decode($json);
    }
    if ($lib == null) {
        $lib = [];
        $lib["movies"] = [];
        $lib["tvShows"] = [];
        $lib = (object) $lib;
    }
    return $lib;
}

/**
 *  Gets a list of all directories found within the $baseDirectory provided
 * @param type $baseDirectory - the full path to the directory that will be searched
 * @return array - list of full paths to each directory found in the provided base directory
 */
function getFoldersFromDirectory($baseDirectory) {

    ob_start();
    $folders = array();
    if ($handle = opendir($baseDirectory)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                $fullPath = $baseDirectory . $file;

                if (is_dir($fullPath)) {
                    $folders[] = $fullPath . "/";
                }
            }
        }
        closedir($handle);
    }
    ob_end_clean();
    return $folders;
}

/**
 * Gets all files in a directory recursively
 * @param String - $dir - the full path to the directory to start in
 * @return array - list of all file paths found in or under this directory
 */
function getVideosFromDir($dir) {
    ob_start();
    $files = array();
    $arr = scandir($dir);
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if (is_dir($dir . $file)) {
                    $dir2 = $dir . $file . "/";
                    $files[] = getVideosFromDir($dir2);
                } else {
                    $d = $dir . $file;
                    if (fileIsValidVideo($d)) {
                        $files[] = $d;
                    }
                }
            }
        }
        closedir($handle);
    }
    ob_end_clean();
    return array_flat($files);
}

/**
 * Flattens out a multi-dimensional array into a single dimensional array
 * @param Array $array - the array to be flattened
 * @return Array - A 1 dimensional array composed of all values found in or under the multi-dimensional array provided
 */
function array_flat($array) {
    $tmp = Array();
    foreach ($array as $a) {
        if (is_array($a)) {
            $tmp = array_merge($tmp, array_flat($a));
        } else {
            $tmp[] = $a;
        }
    }
    return $tmp;
}

/**
 * Determines if the file is a video type supported by the roku
 */
function fileIsValidVideo($file) {
    if ($file != null) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if ($ext == 'mp4' || $ext == 'm4v') {
            return true;
        }
    } return false;
}

function color($text, $color) {
    return "<span style='color: $color;'>$text</span>";
}

function saveImageFromUrl($imageUrl, $imageDest) {
    //if there was no image url, return false
    if ($imageUrl == null) {
        return false;
    }
    //if there was no image url, return false
    if (strlen($imageUrl) < 1) {
        return false;
    }
    //open the poster file from tvdb
    $ch = curl_init($imageUrl);
    //delete the image if it already exists
    if (file_exists($imageDest)) {
        unlink($imageDest);
    }
    $fp = fopen($imageDest, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    $success = fclose($fp);
    //if the file was not successfully saved, delete any file we opened.
    if ($success === false) {
        unlink($imageDest);
    }
    return $success;
}

function writeToLog($message) {
    $logPath = dirname(__FILE__) . "/../log.txt";
    //get current time
    $t = date("Y-m-d H:i:s");
    //get time since last log
    $microtime = microtime(true);
    global $microtimeOfLastLog;
    if ($microtimeOfLastLog == null) {
        $microtimeOfLastLog = $microtime;
    }
    $secondsSinceLastLog = round(($microtime - $microtimeOfLastLog), 4);
    //set the last log time to right now
    $microtimeOfLastLog = $microtime;
    $message = "$t -- $secondsSinceLastLog -- $message\n";
    error_log($message, 3, $logPath);
}

function clearLog() {
    $logPath = dirname(__FILE__) . "/../log.txt";
    file_put_contents($logPath, "");
    writeToLog("Logfile cleared");
}

/**
 * Returns the first element's value found with the specified tag name
 * @param type $doc
 * @param type $tagName
 * @return - the value in the provided tag, or an empty string if the tag was not found
 */
function getXmlTagValue($node, $tagName) {
    $elements = $node->getElementsByTagName($tagName);
    if ($elements != null) {
        $item = $elements->item(0);
        if ($item != null) {
            $val = $item->nodeValue;
            if ($val != null) {
                return $val;
            } else {
                return "";
            }
        }
    }
}

/**
 * Prints the video row as a metadataManager row. 
 * This is used for the metadata manager as well as the metadata manager ajax calls
 * @param Video $v
 */
function printVideoMetadataRow($v) {
    $vSuccess = true;
    ?>
    <tr style="cursor:pointer;" class="videoRow <?php echo $vSuccess ? "success" : "error"; ?>" mediatype="<?php echo $v->mediaType; ?>" baseurl="<?php echo htmlspecialchars($v->baseUrl); ?>" basepath="<?php echo htmlspecialchars($v->basePath); ?>" fullpath="<?php echo htmlspecialchars($v->fullPath); ?>">
        <?php if ($v->mediaType == Enumerations::MediaType_TvEpisode) { ?>
            <td><?php echo $v->showName; ?></td>
        <?php } ?>
        <td><?php echo $v->title; ?></td>
        <td><?php echo $v->nfoFileExists() ? color("Yes", "green") : color("No", "red"); ?></td>
        <td><?php echo $v->posterExists ? color("Yes", "green") : color("No", "red"); ?></td>
        <td><img class="sd" src="<?php echo $v->sdPosterUrl; ?>"/> </td>
        <td><img class="hd" src="<?php echo $v->hdPosterUrl; ?>"/></td>

    </tr>
    <?php
}
?>
