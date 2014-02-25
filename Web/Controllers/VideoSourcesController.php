<?php

include_once(basePath() . "/Code/database/Queries.class.php");

class VideoSourcesController {

    function Index() {
        $sources = Queries::getVideoSources();
        return view((object) ['sources' => $sources]);
    }

    /**
     * Deletes a video source
     * @param type $sourcePath
     */
    function Delete($sourcePath) {
        if ($sourcePath != null) {
            Queries::deleteVideoSource($sourcePath);
        }
        return RedirectToAction("Index");
    }

    /**
     * Adds or edits a video source
     * @param string $basePath
     * @param string $baseUrl
     * @param string $mediaType
     * @param string $securityType
     */
    function AddEditSource($location, $baseUrl, $mediaType, $securityType, $originalLocation) {
        if (empty($originalLocation) === true) {
            Queries::addVideoSource($location, $baseUrl, $mediaType, $securityType);
        } else {
            Queries::updateVideoSource($originalLocation, $location, $baseUrl, $mediaType, $securityType);
        }
        return RedirectToAction("Index");
    }

    /**
     * Determines if a path exists on the server or not
     * @param string $path - the path to check to make sure the server can see
     */
    function PathExistsOnServer($path) {
        $pathExists = file_exists($path);
        return json($pathExists);
    }

}
