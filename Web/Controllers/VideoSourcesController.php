<?php
include_once(basePath() . "/Code/VideoSource.class.php");
include_once(basePath() . "/Code/database/Queries.class.php");

class VideoSourcesController {

    function Index() {
        $sources = VideoSource::GetAll();
        return view((object) ['sources' => $sources]);
    }

    /**
     * Deletes a video source
     * @param type $sourcePath
     */
    function Delete($sourcePath) {

        if ($sourcePath != null) {
            VideoSource::DeleteVideoSource($sourcePath);
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
            VideoSource::Add($location, $baseUrl, $mediaType, $securityType);
        } else {
            VideoSource::Update($originalLocation, $location, $baseUrl, $mediaType, $securityType);
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
