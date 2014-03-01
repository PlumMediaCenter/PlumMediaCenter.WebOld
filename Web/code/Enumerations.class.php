<?php

namespace Enumerations;

Class GeneratePosters {

    const None = "none";
    const Missing = "missing";
    const All = "all";

}

class MediaType {

    const Movie = "Movie";
    const TvShow = "TvShow";
    const TvEpisode = "TvEpisode";

}

class MetadataManagerAction {

    const GeneratePosters = "GeneratePosters";
    const ReloadMetadata = "ReloadMetadata";
    const FetchMetadata = "FetchMetadata";
    const FetchPoster = "FetchPoster";
    const FetchAndGeneratePosters = "FetchAndGeneratePosters";

}

class SecurityType {

    const Anonymous = "Anonymous";
    const LoginRequired = "LoginRequired";

}

class PlayType {

    const Single = "Single";
    const TvShow = "TvShow";
    const Playlist = "Playlist";

}

?>
