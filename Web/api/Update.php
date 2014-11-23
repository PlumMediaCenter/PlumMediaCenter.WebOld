<?php

require(dirname(__FILE__) . '/../code/functions.php');
require(dirname(__FILE__) . '/../code/database/Version.class.php');

$url = "https://api.github.com/repos/twitchbronbron/plumvideoplayer/git/refs/tags";
$options = array('http' => array('user_agent' => 'TwitchBronBron/PlumVideoPlayer'));
$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);
//$response = '[{"ref":"refs/tags/v0.1.0","url":"https://api.github.com/repos/TwitchBronBron/PlumVideoPlayer/git/refs/tags/v0.1.0","object":{"sha":"c2a7f7e6ded9063f0dc3e8987de94b977a83bf9b","type":"tag","url":"https://api.github.com/repos/TwitchBronBron/PlumVideoPlayer/git/tags/c2a7f7e6ded9063f0dc3e8987de94b977a83bf9b"}},{"ref":"refs/tags/v0.1.1","url":"https://api.github.com/repos/TwitchBronBron/PlumVideoPlayer/git/refs/tags/v0.1.1","object":{"sha":"b8e614aef6558941d7ccf3fb046ef3ace429a164","type":"tag","url":"https://api.github.com/repos/TwitchBronBron/PlumVideoPlayer/git/tags/b8e614aef6558941d7ccf3fb046ef3ace429a164"}}]';

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
$currentVersion = '0.1.0';
echo "Our version is $currentVersion. GitHub latest version is " . $highestTagObject['tag'] . '<br/>';
if ($currentVersion < $highestTagObject['tag']) {
    echo "We need to fetch some updates<br/>";
    loadLatestCode($highestTagObject['sha']);
} else {
    echo "Server is up to date. No update needed.<br/>";
}

function loadLatestCode($sha) {
    $tempDir = dirname(__FILE__) . '/../tmp';
    $zipFolderPath = "$tempDir/server.zip";
    $extractedPath = "$tempDir/extract";
    $extractedWebPath = "$extractedPath/PlumVideoPlayer-$sha/Web";
    $rootWebPath = dirname(__FILE__) . '/..';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    echo "creating a temporary directory: $tempDir<br/>";
    //empty out the directory
    deleteFromDirectory($tempDir . '*');
    $url = "https://github.com/TwitchBronBron/PlumVideoPlayer/archive/$sha.zip";
    file_put_contents($zipFolderPath, fopen($url, 'r'));
    //unzip the archive
    $zip = new ZipArchive;
    if ($zip->open($zipFolderPath) === true) {
        $zip->extractTo($extractedPath);
        $zip->close();
    } else {
        echo 'failed to unzip archive of new version';
        return;
    }
    //copy every file from the extracted web path to the root of this application directory (overwriting every file)
    recurse_copy_overwrite($extractedWebPath, $rootWebPath);
    //clean up the temp directory now that the file updates have finished
    rrmdir($tempDir);

    //run the database update 
    include(dirname(__FILE__) . '/../code/database/CreateDatabase.class.php');
    $createDatabase = new CreateDatabase(config::$dbUsername, config::$dbPassword, config::$dbHost);
    $createDatabase->upgradeDatabase();
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
