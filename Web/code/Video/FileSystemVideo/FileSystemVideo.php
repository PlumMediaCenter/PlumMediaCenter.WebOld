<?php

include_once(dirname(__FILE__) . '/../../lib/php-mp4info/MP4Info.php');
include_once(dirname(__FILE__) . '/../../lib/SimpleImage/SimpleImage.php');
include_once(dirname(__FILE__) . '/../../Interfaces/iVideo.php');


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
abstract class FileSystemVideo implements iVideo {

    const UNKNOWN_MPAA = 'N/A';

    protected $posterFilenames;
    protected $mediaType;
    protected $metadataLoaded = false;
    protected $metadataLoadedFromNfo = false;
    protected $posterPath;
    protected $posterUrl;
    protected $nfoReader = null;
    protected $metadataPosterUrl = null;
    protected $metadataRunningTimeSeconds = null;
    protected $posterDestinationPath;
    //Indicates whether loadMetadata has been called yet or not
    protected $metadataWasLoaded = false;

    /** Database Fields */
    protected $videoId = null;
    protected $title;
    protected $plot;
    protected $mpaa;
    protected $releaseDate;
    protected $genres;
    protected $sourceUrl;
    protected $sourcePath;
    protected $path;
    protected $url;

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

        //$this->posterUrl = $this->getPosterUrl();

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
        if ($this->videoId !== null) {
            $posterFilePath = FileSystemVideo::posterDestinationPath() . "/$this->videoId.jpg";
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

            $nfoPath = $this->getExistingNfoPath();
            //no nfo file was found. look online for the metadata
            if ($nfoPath === null) {
                $iVideoMetadata = $this->getMetadataFetcher();
                $this->metadataLoadedFromNfo = false;
            } else {
                $iVideoMetadata = $this->getNfoReader();
                $this->metadataLoadedFromNfo = true;
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

                $this->genres = $iVideoMetadata->genres();
            } else {
                //the metadata interface was empty. 
                $this->genres = [];
                $this->title = $this->getFilename();
                $this->plot = null;
                $this->mpaa = FileSystemVideo::UNKNOWN_MPAA;
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
    private function getRunningTimeSeconds() {
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
     * Returns the date of the last time the nfo file was modified
     * @return \DateTime
     */
    public function getMetadataLastModifiedDate() {
        return $this->getModifiedDate($this->getExistingNfoPath());
    }

    /**
     * Returns the date of the last time the poster file was modified
     * @return \DateTime
     */
    public function getPosterLastModifiedDate() {
        $existingPosterPath = $this->getExistingPosterPath();
        return $this->getModifiedDate($existingPosterPath);
    }

    /**
     * Generates the text-only poster for the size of the media type specified
     * @param \Enumerations\MediaType $mediaType
     */
    protected function generateTextOnlyPosterByType($mediaType) {
        $paddingX = 10;
        $paddingY = 10;
        $width = 1000;
        $height = 1500;
        switch ($mediaType) {
            case \Enumerations\MediaType::TvEpisode:
                $width = 400;
                $height = 225;
                break;
            default:
                $width = 1000;
                $height = 1500;
                break;
        }
        $title = $this->title;

        $publicPosterPath = $this->getPublicPosterPath();
        $text = $this->title;
        $img_width = $width;

        $font = dirname(__FILE__) . '/geo_1.ttf';
        // Create the image
        // $text = 'Testing... a very long text.. Testing... a very long text.. Testing... a very long text.. Testing... a very long text..';
        $text = "Hello";
        $curTextLen = strlen($text);
        $limit = 35;
        $characterCount = ($curTextLen > $limit) ? $limit : $curTextLen;
        $img_height = $height;

        $im = imagecreatetruecolor($img_width, $img_height);
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, $img_width, $img_height, $white);
//
//        $charSpacingX = 7;
//        $charSpacingY = 5;
        $charSize = 700;
//
//        $xPos = $paddingX;
//        $yPos = $paddingY + $charSize;
//        //loop through each character in the string
//        for ($i = 1; $i <= $characterCount; $i++) {
//            $xPos = $xPos + $charSize;
//            //if this character will run off of the side, return to the next line and start again
//            if (($xPos + $charSize + $paddingX) > $width) {
//                $xPos = $paddingX;
//                $yPos = $yPos + $charSize + $charSpacingY;
//            }
//            echo " ($xPos, $yPos)";
//            $textN = substr($text, ($limit * ($i - 1)), $limit);
//            imagettftext($im, $charSize, 0, $xPos, $yPos, $grey, $font, $textN);
//            imagettftext($im, $charSize, 0, $xPos - 1, $yPos, $grey, $font, $textN);
//
//
//            // Add the text
//            //   imagettftext($im, $charSize, 0, $xPos, $yPos, $black, $font, $textN);
//        }
        $this->write_multiline_text($im, $charSize, $black, $font, $text, 10, 10, $width - $paddingX);
        // Using imagepng() results in clearer text compared with imagejpeg()
        imagepng($im, $publicPosterPath);
        imagedestroy($im);
    }

    function write_multiline_text($image, $font_size, $color, $font, $text, $start_x, $start_y, $max_width) {
        //split the string 
        //build new string word for word 
        //check everytime you add a word if string still fits 
        //otherwise, remove last word, post current string and start fresh on a new line 
        $words = explode(" ", $text);
        $string = "";
        $tmp_string = "";

        for ($i = 0; $i < count($words); $i++) {
            $tmp_string .= $words[$i] . " ";

            //check size of string 
            $dim = imagettfbbox($font_size, 0, $font, $tmp_string);

            if ($dim[4] < $max_width) {
                $string = $tmp_string;
            } else {
                $i--;
                $tmp_string = "";
                imagettftext($image, 11, 0, $start_x, $start_y, $color, $font, $string);

                $string = "";
                $start_y += 22; //change this to adjust line-height. Additionally you could use the information from the "dim" array to automatically figure out how much you have to "move down" 
            }
        }
        imagettftext($image, 11, 0, $start_x, $start_y, $color, $font, $string); //"draws" the rest of the string 
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
            $finalUrl = baseUrl() . "/Content/Images/posters/$this->videoId.jpg";
        }
        return FileSystemVideo::EncodeUrl($finalUrl);
    }

    function getSdPosterUrl() {
        $finalUrl = "";
        $videoId = $this->videoId();
        $posterPath = $this->getPosterPath();
        if ($posterPath === null) {
            $finalUrl = $this->getBlankPosterUrl("-sd");
        } else {
            $finalUrl = baseUrl() . "/Content/Images/posters/$videoId-sd.jpg";
        }
        return FileSystemVideo::EncodeUrl($finalUrl);
    }

    function getHdPosterUrl() {
        $finalUrl = "";
        $videoId = $this->videoId();

        $posterPath = $this->getPosterPath();
        if ($posterPath === null) {
            $finalUrl = $this->getBlankPosterUrl("-hd");
        } else {
            $finalUrl = baseUrl() . "/Content/Images/posters/$videoId-hd.jpg";
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
        $v->runningTimeSeconds = $this->getRunningTimeSeconds();
        $v->plot = $this->plot();
        $v->path = $this->path();
        $v->url = $this->getUrl();
        $v->filetype = $this->getFiletype();
        $v->metadataLastModifiedDate = $this->getMetadataLastModifiedDate();
        $v->posterLastModifiedDate = $this->getPosterLastModifiedDate();
        $v->posterLoadedFromFileSystem = $this->posterExistsOnFileSystem();
        $v->mpaa = $this->mpaa();
        $v->releaseDate = $this->releaseDate();
        $v->mediaType = $this->mediaType();
        $v->videoSourcePath = $this->sourcePath();
        $v->videoSourceUrl = $this->sourceUrl();
        $v->save();
        //save the video id to the property so we can use it in some other functions
        $this->videoId = $v->videoId;


        $v->sdPosterUrl = $this->getSdPosterUrl();
        $v->hdPosterUrl = $this->getHdPosterUrl();
        $v->metadataLoadedFromNfo = $this->metadataLoadedFromNfo;
        $v->save();

        //clear out any pre-existing genres (only applies when this is an existing video being re-saved
        \orm\VideoGenre::table()->delete(array('video_id' => array($this->videoId)));

        //save each genre
        foreach ($this->genres as $genre) {
            //save this genre to this movie
            $vg = new \orm\VideoGenre();
            $vg->name = $genre;
            $vg->videoId = $v->id;
            $vg->save();
        }


        $this->copyPosters();

        //if this movie still does not have a public poster after copyPosters has completed, 
        //we need to generate a text-only poster for it
        $publicPosterPath = $this->getPublicPosterPath();
        if (file_exists($publicPosterPath) === false) {
            $this->generateTextOnlyPosterByType($this->mediaType);
            $this->copyPosters();
        }
    }

    public function getPublicPosterPath() {
        return FileSystemVideo::posterDestinationPath() . "/$this->videoId.jpg";
    }

    public function delete() {
        $destinationFolderPath = dirname(__FILE__) . '/../../../Content/Images/posters';
        $videoId = $this->videoId();
        $sdPosterPath = "$destinationFolderPath/$videoId-sd.jpg";
        $hdPosterPath = "$destinationFolderPath/$videoId-hd.jpg";
        $posterPath = "$destinationFolderPath/$videoId.jpg";
        //just try to delete the posters. if they don't exist, we don't care. Hide the error messages
        @unlink($sdPosterPath);
        @unlink($hdPosterPath);
        @unlink($posterPath);

        //delete every VideoGenre record referencing this video
        \orm\VideoGenre::table()->delete(array('video_id' => array($videoId)));

        //delete every watchVideo record referencing this video
        \orm\WatchVideo::table()->delete(array('video_id' => array($videoId)));

        //finally, delete the video itself
        \orm\Video::table()->delete(array('video_id' => array($videoId)));

        //clear the local videoId so we don't think this video still exists in the db
        $this->videoId = null;
    }

    public function copyPosters() {
        $this->loadMetadata();
        //save the hd poster.
        $posterPath = "$this->posterDestinationPath/$this->videoId.jpg";
        $sdPosterPath = "$this->posterDestinationPath/$this->videoId-sd.jpg";
        $hdPosterPath = "$this->posterDestinationPath/$this->videoId-hd.jpg";

        $this->savePoster($posterPath);
        $this->savePoster($sdPosterPath, \Enumerations\PosterSizes::RokuSDWidth, \Enumerations\PosterSizes::RokuSDHeight, $posterPath);
        $this->savePoster($hdPosterPath, \Enumerations\PosterSizes::RokuHDWidth, \Enumerations\PosterSizes::RokuHDHeight, $posterPath);
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
    private function savePoster($destination, $maxWidth = null, $maxHeight = null, $posterPath = null) {
        $posterPath = ($posterPath === null) ? $this->getPosterPath() : $posterPath;
        if ($posterPath != null) {
            $image = new \abeautifulsite\SimpleImage();
            //load the image
            try {
                $success = $image->load($posterPath);
                if ($maxWidth !== null && $maxHeight !== null) {
                    //resize the image
                    $image->best_fit($maxWidth, $maxHeight);
                }

                $image->save($destination);
            } catch (ErrorException $e) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /* iVideo Implementation */

    /**
     * Get the videoId from the database of this video
     * @return int - the video id of this video in the database
     */
    public function videoId() {
        //if the video id of this video is set already, return that
        if ($this->videoId !== null) {
            return $this->videoId;
        } else {
            //the video id is not set. Check the database to see if this video is in there
            $video = \orm\Video::find_by_path($this->path);
            if ($video !== null) {
                return $video->videoId;
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
     * Gets whether the metadata for this video was loaded from an nfo file or not.
     * @return boolean - whether the metadata for this video was loaded from an nfo file or not.
     */
    function metadataLoadedFromNfo() {
        return $this->metadataLoadedFromNfo;
    }

    /**
     * Get the list of genres for this video
     * @return string[] - the list of genres for this video
     */
    public function genres() {
        $this->loadMetadata();
        return $this->genres;
    }

    /* End iVideo functions */
}
