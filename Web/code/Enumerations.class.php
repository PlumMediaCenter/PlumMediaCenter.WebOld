<?php

namespace Enumerations;

class Enumeration {

    static $cache = [];

    static function IsValid($value) {
        //get the name of the subclass calling this
        $enumType = get_called_class();
        //if we have not yet calculated the values of this enumeration, calculate them and store them in cache
        if (isset(\Enumerations\Enumeration::$cache[$enumType]) === false) {
            $refl = new \ReflectionClass($enumType);
            $enumValues = $refl->getConstants();
            \Enumerations\Enumeration::$cache[$enumType] = $enumValues;
        }

        //grab the enum values for this enum type
        $values = \Enumerations\Enumeration::$cache[$enumType];
        //if the value specified is in the list of enum values for this enum, return true. otherwise, the value is not a valid enum value
        if (in_array($value, $values) === true) {
            return true;
        } else {
            return false;
        }
    }

}

Class GeneratePosters extends Enumeration {

    const None = "none";
    const Missing = "missing";
    const All = "all";

}

class MediaType extends Enumeration {

    const Movie = "Movie";
    const TvShow = "TvShow";
    const TvEpisode = "TvEpisode";

}

class MetadataManagerAction extends Enumeration {

    const GeneratePosters = "GeneratePosters";
    const ReloadMetadata = "ReloadMetadata";
    const FetchMetadata = "FetchMetadata";
    const FetchPoster = "FetchPoster";
    const FetchAndGeneratePosters = "FetchAndGeneratePosters";

}

class SecurityType extends Enumeration {

    const Anonymous = "Anonymous";
    const LoginRequired = "LoginRequired";

}

class PlayType extends Enumeration {

    const Single = "Single";
    const TvShow = "TvShow";
    const Playlist = "Playlist";

}

class PosterSizes extends Enumeration {

    const RokuSDWidth = 110;
    const RokuSDHeight = 150;
    const RokuHDWidth = 210;
    const RokuHDHeight = 270;

}

?>
