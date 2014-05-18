<?php

require_once(dirname(__FILE__) . '/../../../code/VideoSource.class.php');

class TestVideoSource extends UnitTestCase {

    function setUp() {
        
    }

    function testAddInvalid() {
        $path = str_replace("\\", "/", dirname(__FILE__) . '/');
        $url = "http://www.google.com/";
        $mediaType = Enumerations\MediaType::Movie;
        $securityType = Enumerations\SecurityType::Anonymous;

        //Test when each individual field is set to null on its own
        {
            $loc = new orm\VideoSource();
            $loc->location = null;
            $this->assertFalse($loc->save());

            $loc = new orm\VideoSource();
            $loc->baseUrl = null;
            $this->assertFalse($loc->save());

            $loc = new orm\VideoSource();
            $loc->mediaType = null;
            $this->assertFalse($loc->save());

            $loc = new orm\VideoSource();
            $loc->securityType = null;
            $this->assertFalse($loc->save());
        }

        //test when all fields are set to null 
        {
            $loc = new orm\VideoSource();
            $loc->location = null;
            $loc->baseUrl = null;
            $loc->mediaType = null;
            $loc->securityType = null;
            $this->assertFalse($loc->save());
        }

        //test when all fields are set to null except one, which is set to a valid value
        {

            $loc = new orm\VideoSource();
            $loc->location = $path;
            $loc->baseUrl = null;
            $loc->mediaType = null;
            $loc->securityType = null;
            $this->assertFalse($loc->save());

            $loc = new orm\VideoSource();
            $loc->location = null;
            $loc->baseUrl = $url;
            $loc->mediaType = null;
            $loc->securityType = null;
            $this->assertFalse($loc->save());

            $loc = new orm\VideoSource();
            $loc->location = null;
            $loc->baseUrl = null;
            $loc->mediaType = $mediaType;
            $loc->securityType = null;
            $this->assertFalse($loc->save());

            $loc = new orm\VideoSource();
            $loc->location = null;
            $loc->baseUrl = null;
            $loc->mediaType = null;
            $loc->securityType = $securityType;
            $this->assertFalse($loc->save());
        }

        //Test when all values are valid except for one
        {
            //Location
            {
                //directory must exist
                $loc = new orm\VideoSource();
                $loc->location = "invalidDirectory";
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                //only forward slashes are allowed. 
                $loc = new orm\VideoSource();
                $loc->location = str_replace("/", "\\", $path);
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                //must END in a slash.
                $loc = new orm\VideoSource();
                $loc->location = dirname(__FILE__);
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                //cannot be empty string
                $loc = new orm\VideoSource();
                $loc->location = '';
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                //cannot be whitespace string
                $loc = new orm\VideoSource();
                $loc->location = ' ';
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                //or a tab string
                $loc = new orm\VideoSource();
                $loc->location = '\t';
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());
            }


            //baseUrl
            {
                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = 'invalidUrl';
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = 'http://';
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = 'htt:';
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = 'domain.com';
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = 'domain.com';
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                //must end in a slash
                //cannot be empty, whitespace or tab
                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = '';
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = '  ';
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = '\t';
                $loc->mediaType = $mediaType;
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());
            }

            //media type
            {
                //invalid media type
                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = $url;
                $loc->mediaType = 'MediaTypeThatDoesNotExist';
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                //differ the value by case
                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = $url;
                $loc->mediaType = strtoupper(\Enumerations\MediaType::Movie);
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                //cannot be empty, whitespace or tab
                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = $url;
                $loc->mediaType = '';
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = $url;
                $loc->mediaType = ' ';
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = $url;
                $loc->mediaType = ' \t';
                $loc->securityType = $securityType;
                $this->assertFalse($loc->save());
            }
            //security type
            {
                //invalid media type
                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = 'SecurityTypeThatDoesNotExist';
                $this->assertFalse($loc->save());

                //differ the value by case
                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = strtoupper(\Enumerations\SecurityType::Anonymous);
                $this->assertFalse($loc->save());

                //cannot be empty, whitespace or tab

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = '';
                $this->assertFalse($loc->save());

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = ' ';
                $this->assertFalse($loc->save());

                $loc = new orm\VideoSource();
                $loc->location = $path;
                $loc->baseUrl = $url;
                $loc->mediaType = $mediaType;
                $loc->securityType = '\t';
                $this->assertFalse($loc->save());
            }
        }
    }

    function testAddValid() {
        $path = str_replace("\\", "/", dirname(__FILE__) . '/');
        $url = "http://www.google.com";
        $mediaType = Enumerations\MediaType::Movie;
        $securityType = Enumerations\SecurityType::Anonymous;
        //Url
        {
            $loc = new orm\VideoSource();
            $loc->location = $path;
            $loc->baseUrl = 'http://localhost/';
            $loc->mediaType = $mediaType;
            $loc->securityType = $securityType;
            $this->assertTrue($loc->save());

            //load the record and make sure the values are correct
            $loaded = orm\VideoSource::find($path);
            $this->assertEqual($path, $loaded->location);
            $this->assertEqual('http://localhost/', $loaded->baseUrl);
            $this->assertEqual($mediaType, $loaded->mediaType);
            $this->assertEqual($securityType, $loaded->securityType);
            $loc->delete();

            $loc = new orm\VideoSource();
            $loc->location = $path;
            $loc->baseUrl = 'http://sub.domain.com/';
            $loc->mediaType = $mediaType;
            $loc->securityType = $securityType;
            $this->assertTrue($loc->save());

            //load the record and make sure the values are correct
            $loaded = orm\VideoSource::find($path);
            $this->assertEqual($path, $loaded->location);
            $this->assertEqual('http://sub.domain.com/', $loaded->baseUrl);
            $this->assertEqual($mediaType, $loaded->mediaType);
            $this->assertEqual($securityType, $loaded->securityType);
            $loaded->delete();
        }
    }

    function testUpdate() {
        $path = str_replace("\\", "/", dirname(__FILE__) . '/');
        $url = "http://www.google.com/";
        $mediaType = Enumerations\MediaType::Movie;
        $securityType = Enumerations\SecurityType::Anonymous;

        //add a new record
        $loc = new orm\VideoSource();
        $loc->location = $path;
        $loc->baseUrl = $url;
        $loc->mediaType = $mediaType;
        $loc->securityType = $securityType;
        $this->assertTrue($loc->save());

        //update every field, including the key
        $newLoc = orm\VideoSource::find($path);
        $newLoc->location = "$path/../";
        $newLoc->baseUrl = 'http://sub.domain.com/somewhere/';
        $newLoc->mediaType = $mediaType;
        $newLoc->securityType = $securityType;
        $this->assertTrue($newLoc->save());

        $loc->delete();
        $newLoc->delete();
    }

}
