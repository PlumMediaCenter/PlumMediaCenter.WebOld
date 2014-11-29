<?php

require(dirname(__FILE__) . '/../code/functions.php');
require(dirname(__FILE__) . '/../code/database/Version.class.php');
$repoOwner = config::$repoOwner;
$repoName = config::$repoName;
$url = "https://api.github.com/repos/$repoOwner/$repoName/git/refs/tags";
$options = array('http' => array('user_agent' => "$repoOwner/$repoName"));
$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

$tagObjects = json_decode($response, true);
$finalTags = [];
foreach ($tagObjects as $tagObject) {
    $finalTags[] = ['sha' => $tagObject['object']['sha'], 'tag' => str_replace('refs/tags/v', '', $tagObject['ref'])];
}

$highestTagObject = ['tag' => '0.0.0'];
foreach ($finalTags as $tagObject) {
    if ($tagObject['tag'] > $highestTagObject['tag']) {
        $highestTagObject = $tagObject;
    }
}

//get the current version of this server
$currentVersion = Version::GetVersion(config::$dbHost, config::$dbUsername, config::$dbPassword, config::$dbName);

echo "Our version is $currentVersion. GitHub latest version is " . $highestTagObject['tag'] . '<br/>';
if ($currentVersion < $highestTagObject['tag']) {
    echo "We need to fetch some updates<br/>";
    loadLatestCode($highestTagObject['sha']);
    echo "Updated server to version " . $highestTagObject['tag'] . '<br/>';
} else {
    echo "Server is up to date. No update needed.<br/>";
}

function loadLatestCode($sha) {
    $tempDir = dirname(__FILE__) . '/../tmp';
    $zipFolderPath = "$tempDir/server.zip";
    $extractedPath = "$tempDir/extract";
    $repoName = config::$repoName;
    $extractedWebPath = "$extractedPath/$repoName-$sha";
    $rootWebPath = dirname(__FILE__) . '/..';
    echo "Ensuring that temp directory exists: '$tempDir'<br/>";
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    //empty out the directory
    echo 'Emptying out temp directory<br/>';
    deleteFromDirectory($tempDir . '*');
    $repoOwner = config::$repoOwner;
    $repoName = config::$repoName;
    $url = "https://github.com/$repoOwner/$repoName/archive/$sha.zip";
    echo "Downloading latest server code from '$url'<br/>";
    file_put_contents($zipFolderPath, fopen($url, 'r'));
    //unzip the archive
    $zip = new ZipArchive;
    echo 'Download complete. Extracting zip archive of server code<br/>';
    if ($zip->open($zipFolderPath) === true) {
        $zip->extractTo($extractedPath);
        $zip->close();
    } else {
        echo 'failed to unzip archive of new version';
        return;
    }
    echo 'Extract complete<br/>';
    echo 'Overwriting files on server with latest code<br/>';
    //copy every file from the extracted web path to the root of this application directory (overwriting every file)
    recurse_copy_overwrite($extractedWebPath, $rootWebPath);
    echo 'Overwrite complete. Cleaning up temp directory<br/>';
    //clean up the temp directory now that the file updates have finished
    rrmdir($tempDir);

    //run the database update 
    echo 'Updating database<br/>';
    include(dirname(__FILE__) . '/../code/database/CreateDatabase.class.php');
    $createDatabase = new CreateDatabase(config::$dbUsername, config::$dbPassword, config::$dbHost);
    $createDatabase->upgradeDatabase();
    echo 'Database update complete<br/>';
}

function deleteFromDirectory($globPattern) {
    $files = glob($globPattern); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file))
            unlink($file); // delete file
    }
}

function recurse_copy_overwrite($src, $dst) {
    $dir = opendir($src);
    //make the directory if it doesn't already exist
    @mkdir($dst);
    while (false !== ( $file = readdir($dir))) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy_overwrite($src . '/' . $file, $dst . '/' . $file);
            } else {
                //don't overwrite the config file
                if (strpos($file, 'config.php') > -1) {
                    
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
    }
    closedir($dir);
}

// When the directory is not empty:
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir")
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}
