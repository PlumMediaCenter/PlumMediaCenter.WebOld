<?php

include_once(dirname(__FILE__) . '/../../lib/php-mp4info/MP4Info.php');
include_once(dirname(__FILE__) . '/../../lib/SimpleImage/SimpleImage.php');
include_once(dirname(__file__) . '/../../lib/PHPImageWorkshop/ImageWorkshop.php');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FilesystemVideo
 *
 * @author bplumb
 */
abstract class FSVideo {

    const UNKNOWN_MPAA = 'N/A';
    //for videos that don't have nfo files in their directories, refresh those videos' db metadata by this value
    const REFRESH_VIDEO_FROM_WEB_FREQUENCY_DAYS = 60;

    protected $posterFilenames;
    protected $mediaType;
    protected $metadataLoaded = false;
    protected $posterPath;
    protected $posterUrl;
    protected $nfoReader = null;
    protected $metadataPosterUrl = null;
    protected $metadataRunningTimeSeconds = null;
    protected $posterDestinationPath;
    //Indicates whether loadMetadata has been called yet or not
    protected $metadataWasLoaded = false;

    /** An object wrapping the database table */
    protected $dbVideo;

    function __construct($path, $sourcePath, $sourceUrl) {
        $this->posterDestinationPath = FileSystemVideo::posterDestinationPath();
        //save the sourceUrl
        $this->sourceUrl = $sourceUrl;
        //save the sourcePath
        $this->sourcePath = str_replace("\\", "/", realpath($sourcePath)) . "/";
        //determine the full path
        $fullPathRealPath = realpath($path);

        if ($fullPathRealPath === false) {
            //we don't really care if the video actually exists at the path specified. Don't throw the exception
            // throw new Exception("Unable to construct a video object at path $path: path does not exist");
        }
        //save the full path
        $this->path = str_replace("\\", "/", $fullPathRealPath);

        //if this video does not exist, throw a new exception
        if (file_exists($this->path) === false) {
            //we don't really care if the video actually exists at the path specified. Don't throw the exception
            //throw new Exception("Video does not exist at path $this->path");
        }

        //generate the video file url
        $relativePath = str_replace($this->sourcePath, "", $this->path);
        $this->url = $this->sourceUrl . $relativePath;

        //retrieve the poster path if the video has a poster in its folder with it
        $this->posterPath = $this->getExistingPosterPath();

        $this->genres = [];
    }

    /**
     * Returns the path to the poster folder
     * @return type
     */
    static function posterDestinationPath() {
        return dirname(__FILE__) . '/../../../Content/Images/posters';
    }

    static function posterDestinationUrl() {
        return baseUrl() . '/Content/Images/posters';
    }

    /*
     * Get the media type of the video
     * @return string - the media type of the video
     */

    function mediaType() {
        return $this->mediaType;
    }

    /**
     * Determine if the video exists on the filesystem or not
     * @return boolean - true if the video exists on the filesystem, false if it does not
     */
    function videoExists() {
        if (file_exists($this->path) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns the full path to the existing poster, if this video has a poster.
     * This function searches through the list of possible poster filenames until it finds
     * one that matches. If no matching poster was found, null is returned.
     * @return string|null - full path to poster if one of possible filenames was found, null if not found
     */
    public function getExistingPosterPath() {
        $possiblePosterFilenames = $this->getPossiblePosterFilenames();
        $basePath = $this->getContainingFolderPath();
        foreach ($possiblePosterFilenames as $posterFilename) {
            $posterFilePath = "$basePath/$posterFilename";
            if (file_exists($posterFilePath) === true) {
                return $posterFilePath;
            }
        }
        //at this point, none of the expected posters are present. See if there is a poster
        //in the public web poster folder for this video
        if ($this->video_id !== null) {
            $posterFilePath = FileSystemVideo::posterDestinationPath() . "/$this->video_id.jpg";
            if (file_exists($posterFilePath) === true) {
                return $posterFilePath;
            }
        }
        return null;
    }

    /**
     * Get the full path to the parent folder of this video
     * @return string 
     */
    protected function getContainingFolderPath() {
        $containingFolderPath = dirname($this->path);
        return $containingFolderPath;
    }

    /**
     * Returns the filename of the file provided to the video
     * @return string - the filename of the file provided to the video
     */
    protected abstract function getFilename();

    /**
     *  Returns an array of possible names of poster files.
     *      Checks for files in this order:
     *      <filename>-poster.(jpg/png)
     *      poster.(jpg/png)
     *      folder.jpg
     * @return string - an array of possible allowed filenames of posters for this video, in 
     *                  priority order from highest priority to lowest priority.
     */
    protected function getPossiblePosterFilenames() {
        $containingFolderPath = $this->getContainingFolderPath();
        $filename = $this->getFilename();
        $posterFilenames = array(
            "$filename.jpg",
            "$filename.png",
            "poster.jpg",
            "poster.png",
            "folder.png",
            "folder.jpg"
        );
        return $posterFilenames;
    }

    /**
     * Returns the full url to the video file
     * @return string - the full url to the video file
     */
    public abstract function getUrl();

    /**
     * Retrieves the name of the blank poster that will be used if no poster was found for this video
     */
    protected abstract function getBlankPosterName();

    /**
     * Forces each child class to load their corresponding metadata fetcher class
     * @return MetadataFetcher
     */
    protected abstract function getMetadataFetcher();

    /**
     * Has the child object fetch its nfo reader class.
     */
    protected abstract function getNfoReader();

    /**
     * Returns the url to the folder that contains all of the blank posters
     * @return string - the url to the folder containing all of the blank posters
     */
    protected function getBlankPosterBaseUrl() {
        $url = fileUrl(__FILE__) . "/../Content/Images/posters/blankPosters";
        $url = url_remove_dot_segments($url);
        return FileSystemVideo::EncodeUrl($url);
    }

    /**
     * Replaces any invalid url characters with encoded url characters
     * @param string $url - the subject url to be encoded
     * @return string - the treated url
     */
    public static function EncodeUrl($url) {
        return str_replace(" ", "%20", $url);
    }

    /* First checks to see if there is an NFO file in the normal places.
     * First will check to see if an nfo file of the same name as the video exists.
     * If not, then it will check for ANY nfo file, and use the first one it finds.
     * Returns the path to an existing nfo file, or null if one was not found.
     */

    public function getExistingNfoPath() {
        $nfoPath = null;
        //check to see if there is an nfo file with the same name as this video in the same directory.
        $filename = pathinfo($this->path, PATHINFO_FILENAME);
        $containingFolderPath = $this->getContainingFolderPath();
        $sameNameNfoPath = "$containingFolderPath/$filename.nfo";
        if (file_exists($sameNameNfoPath) === true) {
            $nfoPath = $sameNameNfoPath;
        } else {//look for ANY nfo file in the folder.
            $files = glob("$containingFolderPath/*.nfo");
            foreach ($files as $nfoFilePath) {
                $nfoPath = $nfoFilePath;
                break;
            }
        }
        return $nfoPath;
    }

    /**
     * Gets the full url to the parent folder of this video. 
     * @return string - the full url to the parent folder of this video
     */
    public function getContainingFolderUrl() {
        $containingFolderUrl = dirname($this->url);
        return $containingFolderUrl;
    }

    /**
     * Loads the metadata into memory. 
     * First will check to see if an nfo file of the same name as the video exists.
     * If not, then it will check for ANY nfo file, and use the first one it finds.
     * If not, then the video will check the online db and retrieve any metadata from there. 
     * @param boolean $force - indicates whether the metadata should be force reloaded. if false, metadata 
     *                          will only be loaded if this is the first time this function has been called
     */
    public function loadMetadata($force = false) {
        if ($this->metadataWasLoaded === false || $force === true) {
            /* @var iVideoMetadata $iVideoMetadata */
            $iVideoMetadata = null;

            switch ($this->determineMetadataSource()) {
                case \Enumerations\MetadataSource::NFO:
                    $iVideoMetadata = $this->getNfoReader();
                    $this->metadataSource = \Enumerations\MetadataSource::NFO;
                    break;
                case \Enumerations\MetadataSource::Web:
                    $iVideoMetadata = $this->getMetadataFetcher();
                    break;
                case \Enumerations\MetadataSource::None:
                    break;
            }

            if ($iVideoMetadata !== null) {
                //extract all of the video information from the fetcher or reader
                $this->title = $iVideoMetadata->title();
                $this->plot = $iVideoMetadata->plot();
                $this->mpaa = $iVideoMetadata->mpaa();
                $this->releaseDate = $iVideoMetadata->releaseDate();
                $this->metadataRunningTimeSeconds = $iVideoMetadata->runningTimeSeconds();
                $this->metadataLoaded = true;
                $this->metadataPosterUrl = $iVideoMetadata->posterUrl();

                $this->metadataSource = \Enumerations\MetadataSource::Web;

                $this->genres = $iVideoMetadata->genres();
            } else {
                //the metadata interface was empty. 
                $this->genres = [];
                $this->title = $this->getFilename();
                $this->plot = null;
                $this->mpaa = FileSystemVideo::UNKNOWN_MPAA;

                $this->metadataSource = \Enumerations\MetadataSource::None;
            }
        }

        //indicate that the metadata for this video has been loaded
        $this->metadataWasLoaded = true;
    }

    /**
     * Parses the mp4 video's metadata to find the full length of the video in seconds. If the 
     * mp4 file was not able to be parsed, then the metadata length will be used instead. If
     * that is not able to be retrieved, then we will assume this video's length is 0 seconds
     * @return int|boolean - the number of seconds if successful, false if unsuccessful
     */
    public function getRunningTimeSeconds() {
        //make sure that the metadata has been loaded
        $this->loadMetadata();

        $result = 0;
        $fileRunningTime = null;
        //the mp4info class likes to spit out random crap. Hide it with an output buffer
        ob_start();
        $result = @MP4Info::getInfo($this->path);
        ob_end_clean();
        if ($result !== null && $result != false && $result->hasVideo === true) {
            $fileRunningTime = intval($result->duration);
        }
        //if the file runtime was able to be determined based on the file itself, use that.
        if ($fileRunningTime !== null) {
            $result = $fileRunningTime;
        } else {
            //if the metadata has not been loaded yet, load it now
            if ($this->metadataLoaded === false) {
                $this->loadMetadata();
            }
            if ($this->metadataRunningTimeSeconds === null) {
                return 0;
            } else {
                $result = $this->metadataRunningTimeSeconds;
            }
        }
        return $result;
    }

    /**
     * Gets the filetype (a.k.a. extension) of the video
     * @return string - the filetype (a.k.a. extension of the video)
     */
    public function getFiletype() {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    /**
     * Gets the title of this video. If the video's metadata hasn't been fetched yet, it is fetched.
     * @return string
     */
    public function getTitle() {
        if ($this->metadataLoaded === false) {
            $this->loadMetadata();
        }
        return $this->title;
    }

    /**
     * Returns the date of the last time the nfo file was modified. If the metadata source is nfo, returns nfo file modified date.
     * Otherwise returns the current time.
     * @return \DateTime
     */
    public function getMetadataModifiedDate() {
        if ($this->determineMetadataSource() === \Enumerations\MetadataSource::NFO) {
            return $this->getModifiedDate($this->getExistingNfoPath());
        } else {
            $date = new DateTime();
            $date->setTimestamp(time());
            return $date;
        }
    }

    /**
     * Returns the date of the last time the poster file was modified. If the poster source is nfo, returns the date the poster was modified. 
     * Otherwise, it returns the current time. 
     * @return \DateTime
     */
    public function getPosterModifiedDate() {
        if ($this->determinePosterSource() === \Enumerations\MetadataSource::NFO) {
            $existingPosterPath = $this->getExistingPosterPath();
            return $this->getModifiedDate($existingPosterPath);
        } else {
            $date = new DateTime();
            $date->setTimestamp(time());
            return $date;
        }
    }

    /**
     * Returns the date of the last time the file at the path in the parameter was modified
     * @param string $path - the full path to the file
     * @return \DateTime
     */
    private function getModifiedDate($path) {
        $modifiedDate = null;
        $filemtimeValue = filemtime($path);
        if ($filemtimeValue !== false) {
            $modifiedDate = new DateTime();
            $modifiedDate->setTimestamp($filemtimeValue);
        }
        return $modifiedDate;
    }

    public function getBlankPosterUrl($extensionPrefix = "") {
        $blankPosterBaseUrl = $this->getBlankPosterBaseUrl();
        $blankPosterName = $this->getBlankPosterName();
        //rip the extension off
        $ext = pathinfo($blankPosterName, PATHINFO_EXTENSION);
        $name = pathinfo($blankPosterName, PATHINFO_FILENAME);
        $posterUrl = "$blankPosterBaseUrl/$name" . $extensionPrefix . ".$ext";
        return $posterUrl;
    }

    /**
     * Gets the url to the poster for this video. This will ALWAYS return a url. So if 
     * this video does not have a poster, the url returned will point to the blank poster.
     * @return string - the url to the poster for this video. 
     */
    function getPosterUrl() {
        if ($this->videoId() === null) {
            $k = 2;
            // throw new Exception("Unable to get the poster url for this video. It has no video id");
        }
        $finalUrl = "";
        $posterPath = $this->getPosterPath();
        if ($posterPath === null) {
            $finalUrl = $this->getBlankPosterUrl();
        } else {
            $finalUrl = baseUrl() . "/Content/Images/posters/$this->video_id.jpg";
        }
        return FileSystemVideo::EncodeUrl($finalUrl);
    }

    function getSdPosterUrl() {
        $finalUrl = "";
        $video_id = $this->videoId();
        $posterPath = $this->getPosterPath();
        if ($posterPath === null) {
            $finalUrl = $this->getBlankPosterUrl("-sd");
        } else {
            $finalUrl = baseUrl() . "/Content/Images/posters/$video_id-sd.jpg";
        }
        return FileSystemVideo::EncodeUrl($finalUrl);
    }

    function getHdPosterUrl() {
        $finalUrl = "";
        $video_id = $this->videoId();

        $posterPath = $this->getPosterPath();
        if ($posterPath === null) {
            $finalUrl = $this->getBlankPosterUrl("-hd");
        } else {
            $finalUrl = baseUrl() . "/Content/Images/posters/$video_id-hd.jpg";
        }
        return FileSystemVideo::EncodeUrl($finalUrl);
    }

    /**
     * Determines if a poster actually exists on the filesystem
     * @return boolean
     */
    function posterExistsOnFileSystem() {
        $posterPath = $this->getExistingPosterPath();
        return file_exists($posterPath);
    }

    /**
     * Saves this video to the database
     */
    public function save() {
        //see if there is already a video in the db with this file path
        $v = \orm\Video::find_by_path($this->path);
        //if the db found no record, this is a new video. make a new one.
        if ($v == null) {
            $v = new \orm\Video();
        }
        $oldTitle = $v->title;

        $v->title = $this->title();
        $v->running_time_seconds = $this->getRunningTimeSeconds();
        $v->plot = $this->plot();
        $v->path = $this->path();
        $v->url = $this->getUrl();
        $v->filetype = $this->getFiletype();
        $v->mpaa = $this->mpaa();
        $v->release_date = $this->releaseDate();
        $v->media_type = $this->mediaType();
        $v->video_source_path = $this->sourcePath();
        $v->video_source_url = $this->sourceUrl();
        $metadataModifiedDate = $this->getMetadataModifiedDate();
        $v->metadata_modified_date = $metadataModifiedDate;
        $posterModifiedDate = $this->getPosterModifiedDate();
        $v->poster_modified_date = $posterModifiedDate;
        $v->metadata_source = $this->determineMetadataSource();
        $v->poster_source = $this->determinePosterSource();
        $v->save();
        //save the video id to the property so we can use it in some other functions
        $this->video_id = $v->video_id;


        $v->sd_poster_url = $this->getSdPosterUrl();
        $v->hd_poster_url = $this->getHdPosterUrl();
        $v->save();

        //clear out any pre-existing genres (only applies when this is an existing video being re-saved
        \orm\VideoGenre::table()->delete(array('video_id' => array($this->video_id)));

        //save each genre
        foreach ($this->genres as $genre) {
            //save this genre to this movie
            $vg = new \orm\VideoGenre();
            $vg->name = $genre;
            $vg->video_id = $v->video_id;
            $vg->save();
        }


        $this->copyPosters();
//
//        //if this movie still does not have a public poster after copyPosters has completed, 
//        //we need to generate a text-only poster for it
//        $publicPosterPath = $this->getPublicPosterPath();
//        if (file_exists($publicPosterPath) === false) {
//            $this->generateTextOnlyPosterByType($this->mediaType);
//            $this->copyPosters();
//        }
    }

    public function getPublicPosterPath() {
        return FileSystemVideo::posterDestinationPath() . "/$this->video_id.jpg";
    }

    /**
     * Determines the type of metadata that will currently be used for this video. If metadata exists in the 
     * diretory is first, then if a web metadata fetcher exists second, and finally none if neither was found
     * @return \Enumerations\MetadataSource 
     */
    private function determineMetadataSource() {
        //if a metadata file exists on disc, THAT is the source
        if ($this->getExistingNfoPath() !== null) {
            return \Enumerations\MetadataSource::NFO;
        } else {
            $metadataFetcher = $this->getMetadataFetcher();
            //a metadata fetcher was found. We can get metadata from the web. 
            if ($metadataFetcher !== null) {
                return \Enumerations\MetadataSource::Web;
            } else {
                //no metadata fetcher was able to be found. We have to assume there is no metadata available
                return \Enumerations\MetadataSource::None;
            }
        }
    }

    /**
     * Determines the type of poster that will be used for this video. If poster exists in the 
     * diretory is first, then if a web poster fetcher exists second, and finally none if neither was found
     * @return \Enumerations\MetadataSource 
     */
    private function determinePosterSource() {
        //if a metadata file exists on disc, THAT is the source
        if ($this->getExistingPosterPath() !== null) {
            return \Enumerations\MetadataSource::NFO;
        } else {
            $metadataFetcher = $this->getMetadataFetcher();
            //if the metadata fetcher actually has a poster for this video
            $posterUrl = ($metadataFetcher !== null) ? $metadataFetcher->posterUrl() : null;
            if ($posterUrl !== null) {
                return \Enumerations\MetadataSource::Web;
            } else {
                //no metadata fetcher was able to be found or the video had no poster
                // We have to assume there is no poster available
                return \Enumerations\MetadataSource::None;
            }
        }
    }

    /**
     * Determines if the metadata on disc for this video is out of sync with the database
     */
    public function metadataIsOutOfSync() {
        $source = $this->determineMetadataSource();
        $modifiedDate = $this->getMetadataModifiedDate();
        $modifiedDateFromDb = $this->metadataModifiedDateFromDb();
        return $this->posterOrMetadataIsOutOfSync($source, $modifiedDate, $modifiedDateFromDb);
    }

    /**
     * Determines if the poster for this video is out of sync
     */
    private function posterIsOutOfSync() {
        $source = $this->determinePosterSource();
        $modifiedDate = $this->getPosterModifiedDate();
        $modifiedDateFromDb = $this->posterModifiedDateFromDb();
        return $this->posterOrMetadataIsOutOfSync($source, $modifiedDate, $modifiedDateFromDb);
    }

    /**
     * Determines if the poster or metadata with the provided parameters is out of sync
     * @param \Enumerations\MetadataSource $source
     * @param Date $modifiedDate
     * @param Date $modifiedDateFromDb
     * @return boolean - true if the item is out of sync, false if it is still in sync
     */
    private function posterOrMetadataIsOutOfSync($source, $modifiedDate, $modifiedDateFromDb) {
        $isOutOfSync = true;
        //video has a different metadata source than the last time it was saved
        if ($source !== $this->posterSourceFromDb()) {
            $isOutOfSync = true;
        } else {
            if ($source === \Enumerations\MetadataSource::NFO) {
                //if the nfo file changed since last db save, out of sync
                if ($modifiedDate !== $modifiedDateFromDb) {
                    $isOutOfSync = true;
                }
                //both web and none will follow this policy. 
            } else if ($source === \Enumerations\MetadataSource::Web || $source === \Enumerations\MetadataSource::None) {
                //get the modified date from the db. If it's null, set to year 1 to guarentee that metadata is out of sync
                $modifiedDateFromDb = ($modifiedDateFromDb === null) ? strtotime("0001-01-01") : $modifiedDateFromDb;
                //if metadtaa modified date is past the frequency of refresh, go fetch new metadata
                $diff = time() - $modifiedDateFromDb;
                $daysDiff = $diff / (60 * 60 * 24);
                if ($daysDiff > FileSystemVideo::REFRESH_VIDEO_FROM_WEB_FREQUENCY_DAYS) {
                    $isOutOfSync = true;
                } else {
                    $isOutOfSync = false;
                }
            }
        }
        return $isOutOfSync;
    }

    /**
     * Saves any pieces of this video that have changed in the filesystem that are not yet in the database
     */
    public function saveIfChanged() {
        $save = false;
        if ($this->metadataIsOutOfSync()) {
            $this->loadMetadata(true);
            $save = true;
        }

        if ($this->posterIsOutOfSync() === true) {
            $this->copyPosters();
            $save = true;
        }

        if ($save === true) {
            $this->save();
        }
    }

    public function generateTextPoster($title, $destination) {

        if ($title === null) {
            throw new Exception("Unable to generate text poster with no title");
        }
        if ($destination === null) {
            throw new Exception("Unable to generate text poster when no destination was provided");
        }

        $text = $title;
        //append the time to the end of the text for debugging so we can see that the file has changed or not
        $text .= "--" . time();
        $fontPath = dirname(__FILE__) . '/../../../Content/Fonts/Liberation-Mono/LiberationMono-Regular.ttf';
        $fontColor = "000000";
        $textRotation = 0;
        $borderWidth = 25;
        $backgroundColor = "FFFFFF";

        //determine the dimensions of the poster based on the media type of the video
        switch ($this->mediaType()) {
            case \Enumerations\MediaType::TvEpisode:
                $posterWidth = 400;
                $posterHeight = 225;
                break;
            default:
                $posterWidth = 1000;
                $posterHeight = 1500;
                break;
        }
        $maxCharactersPerRow = 20;
        $fontSize = $posterWidth / $maxCharactersPerRow;

        //create the main poster 
        $document = \PHPImageWorkshop\ImageWorkshop::initVirginLayer($posterWidth, $posterHeight);

        $textItems = array();
        $textItems[] = (object) array('x' => null, 'text' => $text, 'layer' => null);
        $textIsReady = false;
        //walk through the text provided and try to fit it all on the poster. This may require splitting the text up into chunks and drawing
        //it in multiple lines.
        while ($textIsReady === false) {
            $textItemCount = count($textItems);
            $finishedCount = 0;
            foreach ($textItems as $key => $textItem) {
                //if the layer has not been created yet, try to create it
                if ($textItem->layer === null) {
                    $thisTextItemText = $textItem->text;
                    $textLength = strlen($thisTextItemText);

                    $twoCharLayer = \PHPImageWorkshop\ImageWorkshop::initTextLayer('AA', $fontPath, $fontSize, $fontColor, $textRotation, $backgroundColor);
                    $singleCharWidth = $twoCharLayer->getWidth() / 2;
                    $layerWidth = $singleCharWidth * $textLength;
                    $xDiff = ($posterWidth - ($borderWidth * 2)) - $layerWidth;

                    //if the text layer wont fit on one line, split it into chunks.
                    if ($xDiff < 0) {
                        //split the text into two equal pieces.
                        $firstHalfEndingChar = floor($textLength / 2);
                        $firstHalfText = substr($thisTextItemText, 0, $firstHalfEndingChar);
                        $secondHalfText = substr($thisTextItemText, $firstHalfEndingChar, $textLength);
                        $textItem->text = $firstHalfText;
                        $secondHalfTextItem = (object) array('x' => null, 'text' => $secondHalfText, 'layer' => null);
                        //insert this item right next to its first half
                        array_splice($textItems, $key + 1, 0, array($secondHalfTextItem));
                        //exit the for loop and start it over.
                        break;
                    } else {
                        //this text item is going to fit onto the poster. Keep it and move on to the next item
                        $textItem->layer = \PHPImageWorkshop\ImageWorkshop::initTextLayer($thisTextItemText, $fontPath, $fontSize, $fontColor, $textRotation, $backgroundColor);
                        //calculate the x for the starting position of the text so that it is centered
                        $textItem->x = ($posterWidth / 2) - ($layerWidth / 2);
                        //this layer is finished. 
                        $finishedCount += 1;
                        //($outerBoxWidth / 2) - ($boxWidth / 2)
                    }
                } else {
                    //this layer is finished
                    $finishedCount += 1;
                }
            }
            //the number of finished items equals the number of expected finished items. Exit the while loop
            if ($finishedCount === $textItemCount) {
                $textIsReady = true;
            }
        }

        $layerIdx = 1;
        $rowCount = count($textItems);

        $middleRowIndex = floor($rowCount / 2);
        $yMargin = 5;
        $idx = 0;
        //add each layer to the document
        foreach ($textItems as $textItem) {
            $textLayer = $textItem->layer;
            $textLayerHeight = $textLayer->getHeight();
            //calculate the y position for this row of text
            $posterCenter = ( $posterHeight / 2) - ($textLayerHeight / 2);
            $yFactor = $idx - $middleRowIndex;

            $yPos = $posterCenter + ($yFactor * ($textLayerHeight + $yMargin ));

            //add the text layer to the document
            $document->addLayer($layerIdx++, $textItem->layer, $textItem->x, $yPos);
            $idx++;
        }


        //create the border
        $borderLayer = \PHPImageWorkshop\ImageWorkshop::initVirginLayer($document->getWidth(), $document->getHeight()); // This layer will have the width and height of the document
        $borderColor = "000000";
        $horizontalBorderLayer = \PHPImageWorkshop\ImageWorkshop::initVirginLayer($document->getWidth(), $borderWidth, $borderColor);
        $verticalBorderLayer = \PHPImageWorkshop\ImageWorkshop::initVirginLayer($borderWidth, $document->getHeight(), $borderColor);
        $borderLayer->addLayer(1, $horizontalBorderLayer, 0, 0);
        $borderLayer->addLayer(2, $horizontalBorderLayer, 0, 0, 'LB');
        $borderLayer->addLayer(3, $verticalBorderLayer, 0, 0);
        $borderLayer->addLayer(4, $verticalBorderLayer, 0, 0, 'RT');
        $document->addLayer(2, $borderLayer);
        $dirPath = dirname($destination);
        $filename = pathinfo($destination, PATHINFO_FILENAME) . '.' . pathinfo($destination, PATHINFO_EXTENSION);
        $createFolders = true;
        $imageQuality = 100;
        $document->save($dirPath, $filename, $createFolders, $backgroundColor, $imageQuality);
    }

    public function delete() {
        $destinationFolderPath = dirname(__FILE__) . '/../../../Content/Images/posters';
        $video_id = $this->videoId();
        $sdPosterPath = "$destinationFolderPath/$video_id-sd.jpg";
        $hdPosterPath = "$destinationFolderPath/$video_id-hd.jpg";
        $posterPath = "$destinationFolderPath/$video_id.jpg";
        //just try to delete the posters. if they don't exist, we don't care. Hide the error messages
        @unlink($sdPosterPath);
        @unlink($hdPosterPath);
        @unlink($posterPath);

        //delete every VideoGenre record referencing this video
        \orm\VideoGenre::table()->delete(array('video_id' => array($video_id)));

        //delete every watchVideo record referencing this video
        \orm\WatchVideo::table()->delete(array('video_id' => array($video_id)));

        //finally, delete the video itself
        \orm\Video::table()->delete(array('video_id' => array($video_id)));

        //clear the local video_id so we don't think this video still exists in the db
        $this->video_id = null;
    }

    public function copyPosters() {
        $video_id = $this->videoId();
        if ($video_id === -1) {
            throw new Exception("Unable to copy posters for a video that does not yet exist in the database");
        }
        // $this->loadMetadata();
        //save the hd poster.
        $posterDestinationPath = "$this->posterDestinationPath/$video_id.jpg";
        $sdPosterDestinationPath = "$this->posterDestinationPath/$video_id-sd.jpg";
        $hdPosterDestinationPath = "$this->posterDestinationPath/$video_id-hd.jpg";

        $savePosterSuccess = $this->savePoster($posterDestinationPath);

        //if poster saving didn't work, there is no poster to copy. Generate a text only poster for this video
        if ($savePosterSuccess === false) {
            $this->generateTextPoster($this->getTitle(), $posterDestinationPath);
        }
        $this->savePoster($sdPosterDestinationPath, \Enumerations\PosterSizes::RokuSDWidth, \Enumerations\PosterSizes::RokuSDHeight, $posterDestinationPath);
        $this->savePoster($hdPosterDestinationPath, \Enumerations\PosterSizes::RokuHDWidth, \Enumerations\PosterSizes::RokuHDHeight, $posterDestinationPath);
    }

    /**
     * Returns either the url or the path to the poster
     */
    public function getPosterPath() {
        //use a local poster first
        $posterFilePath = $this->getExistingPosterPath();
        $posterUrl = $this->metadataPosterUrl;

        if (file_exists($posterFilePath) === true) {
            return $posterFilePath;
        } else if ($posterUrl !== null) {
            return $posterUrl;
        } else {
            //do nothing. This video will use the standard blank poster
        }
    }

    /**
     * Generates an poster that is sized to the max width and max height specified
     * The existing aspect ratio is retained
     * @param type $width - width in pixels
     * @param type $height - height in pixels
     * @return boolean - true if successful, false if file doesn't exist or failure
     */
    private function savePoster($destination, $maxWidth = null, $maxHeight = null, $posterSourcePath = null) {
        $posterSourcePath = ($posterSourcePath === null) ? $this->getPosterPath() : $posterSourcePath;
        if ($posterSourcePath != null) {
            $image = new \abeautifulsite\SimpleImage();
            //load the image
            try {
                $success = $image->load($posterSourcePath);
                if ($maxWidth !== null && $maxHeight !== null) {
                    //resize the image
                    $image->best_fit($maxWidth, $maxHeight);
                }

                $image->save($destination);
            } catch (ErrorException $e) {
                return false;
            }
        } else {
            
        }
        return true;
    }

    /**
     * Goes to the database and gets the metadata modified date for this video
     * @return null
     */
    public function metadataModifiedDateFromDb() {
        $video_id = $this->videoId();
        if ($video_id !== null) {
            /* @var $result \orm\Video  */
            $result = \orm\Video::find_by_video_id($video_id);
            return $result->metadata_modified_date;
        } else {
            return null;
        }
    }

    /**
     * Goes to the database and gets the poster modified date for this video
     */
    public function posterModifiedDateFromDb() {
        $video_id = $this->videoId();
        if ($video_id !== null) {
            /* @var $result \orm\Video  */
            $result = \orm\Video::find_by_videoId($video_id);
            return $result->poster_modified_date;
        } else {
            return null;
        }
    }

    /**
     * Get the video_id from the database of this video
     * @return int - the video id of this video in the database
     */
    public function videoId() {
        //if the video id of this video is set already, return that
        if ($this->video_id !== null) {
            return $this->video_id;
        } else {
            //the video id is not set. Check the database to see if this video is in there
            $video = \orm\Video::find_by_path($this->path);
            if ($video !== null) {
                return $video->video_id;
            }
        }
        //couldn't find a video id. 
        return null;
    }

    /**
     * Get the title of this video
     * @return string - the title of this video
     */
    public function title() {
        $this->loadMetadata();
        return $this->title;
    }

    /**
     * Get the plot of this video
     * @return string - the plot of this video
     */
    public function plot() {
        $this->loadMetadata();
        return $this->plot;
    }

    /**
     * Get the mpaa rating of this video
     * @return string - the mpaa rating of this video
     */
    public function mpaa() {
        $this->loadMetadata();
        return $this->mpaa;
    }

    /**
     * Get the date the video was originally released
     * @return \DateTime - the date the video was originally released
     */
    function releaseDate() {
        return $this->releaseDate;
    }

    /**
     * Get the running time (in seconds) of the video
     * @return int - the running time (in seconds) of the video
     */
    function runningTimeSeconds() {
        return $this->getRunningTimeSeconds();
    }

    /**
     * Get the path to the video file
     * @return string - the path to the video file
     */
    function path() {
        return $this->path;
    }

    /**
     * Get the source path to the video file
     * @return string - the source path to the video file
     */
    function sourcePath() {
        return $this->sourcePath;
    }

    /**
     * Get the source url to the video file
     * @return string - the source url to the video file
     */
    function sourceUrl() {
        return $this->sourceUrl;
    }

    /**
     * Returns the metadata source that was stored in the database the last time this video was saved.
     * @return \Enumerations\MetadataSource
     */
    function metadataSourceFromDb() {
        return $this->metadataSource;
    }

    /**
     * Returns the metadata source that was stored in the database the last time this video was saved.
     * @return \Enumerations\MetadataSource
     */
    function posterSourceFromDb() {
        return $this->posterSource;
    }

    /**
     * Get the list of genres for this video
     * @return string[] - the list of genres for this video
     */
    public function genres() {
        $this->loadMetadata();
        return $this->genres;
    }

}