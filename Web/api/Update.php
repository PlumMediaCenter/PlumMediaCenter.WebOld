<?php

require(dirname(__FILE__) . '/../code/functions.php');
require(dirname(__FILE__) . '/../code/database/CreateDatabase.class.php');

$url = "https://api.github.com/repos/twitchbronbron/plumvideoplayer/git/refs/tags";
$options = array('http' => array('user_agent' => 'TwitchBronBron/PlumVideoPlayer'));
$context = stream_context_create($options);
//$response = file_get_contents($url, false, $context);
$response = '[{"ref":"refs/tags/v0.1.0","url":"https://api.github.com/repos/TwitchBronBron/PlumVideoPlayer/git/refs/tags/v0.1.0","object":{"sha":"c2a7f7e6ded9063f0dc3e8987de94b977a83bf9b","type":"tag","url":"https://api.github.com/repos/TwitchBronBron/PlumVideoPlayer/git/tags/c2a7f7e6ded9063f0dc3e8987de94b977a83bf9b"}},{"ref":"refs/tags/v0.1.1","url":"https://api.github.com/repos/TwitchBronBron/PlumVideoPlayer/git/refs/tags/v0.1.1","object":{"sha":"b8e614aef6558941d7ccf3fb046ef3ace429a164","type":"tag","url":"https://api.github.com/repos/TwitchBronBron/PlumVideoPlayer/git/tags/b8e614aef6558941d7ccf3fb046ef3ace429a164"}}]';

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
$currentVersion = CreateDatabase::CurrentDbVersion();
$currentVersion = '0.1.0';
if ($currentVersion < $highestTagObject['tag']) {
    echo "Need to update";
    loadLatestCode($highestTagObject['sha']);
} else {
    echo "server is up to date";
}

function loadLatestCode($sha) {
    $tempDir = dirname(__FILE__) . '/../tmp/';
    $zipFolderPath = $tempDir . 'server.zip';
    $extractedPath = $tempDir . 'extract';
    $extractedWebPath = "$extractedPath/PlumVideoPlayer-$sha/Web';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    //empty out the directory
    //deleteFromDirectory($tempDir . '*');
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
}

function deleteFromDirectory($globPattern) {
    $files = glob($globPattern); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file))
            unlink($file); // delete file
    }
}
