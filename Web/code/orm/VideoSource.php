<?php

namespace orm;

/**
 * @property String $securityType
 * @property boolean $refreshVideos
 * @property Enumerations_MediaType $mediaType
 * @property String $location
 * @property String $baseUrl
 */
class VideoSource extends \ActiveRecord\Model {

    public function validate() {
        //location must exist
        if (file_exists($this->location) == false) {
            $this->errors->add('location', "Location '$this->location' does not exist");
        }
        //location may only have linux slashes, no windows slashes
        if (strpos($this->location, '\\') > -1) {
            $this->errors->add('location', "Location may not contain '\\' characters");
        }
        //url must be formatted correctly
        if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $this->baseUrl) != 1) {
            $this->errors->add('baseUrl', "Base Url is not a valid url '$this->baseUrl'.");
        }
        //url must end in a slash
        if (strlen($this->baseUrl) > 0 && substr($this->baseUrl,-1) !== '/') {
            $this->errors->add('baseUrl', "Url must end in '/'");
        }

        //mediaType must be a valid value from the enumeration
        if (\Enumerations\MediaType::IsValid($this->mediaType) === false) {
            $this->errors->add('mediaType', "'$this->mediaType' is not a valid media type");
        }

        //mediaType must be a valid value from the enumeration
        if (\Enumerations\SecurityType::IsValid($this->securityType) === false) {
            $this->errors->add('securityType', "'$this->securityType' is not a valid security type");
        }
    }

    static $validates_presence_of = array(
        array('securityType', 'baseUrl', 'mediaType', 'location', 'baseUrl')
    );
    static $table_name = 'video_source';
    static $primary_key = 'location';
    static $alias_attribute = array(
        'securityType' => 'security_type',
        'baseUrl' => 'base_url',
        'mediaType' => 'media_type',
        'refreshVideos' => 'refresh_videos'
    );

}
