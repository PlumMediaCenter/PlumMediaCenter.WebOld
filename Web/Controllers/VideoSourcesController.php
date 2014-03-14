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
            $loc = orm\VideoSource::find($sourcePath);
            $success = $loc->delete();
            if($success === true){
                Notify::Add("Deleted the the video source at '$sourcePath'", Notify::NOTIFY_STATUS_TYPE_SUCCESS);
            }else{
                Notify::Add("Unable to delete the source at '$sourcePath': ", Notify::NOTIFY_STATUS_TYPE_ERROR);
            }
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
            $loc = new orm\VideoSource();
            $loc->location = $location;
            $loc->baseUrl = $baseUrl;
            $loc->mediaType = $mediaType;
            $loc->securityType = $securityType;
            $success = $loc->save();
        } else {
            $loc = orm\VideoSource::find($originalLocation);
            $loc->location = $location;
            $loc->baseUrl = $baseUrl;
            $loc->mediaType = $mediaType;
            $loc->securityType = $securityType;
            $loc->refreshVideos = true;
            $success = $loc->save();
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
