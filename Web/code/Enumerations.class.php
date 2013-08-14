<?php

class Enumerations {
    //GeneratePosters

    const GeneratePosters_None = "none";
    const GeneratePosters_Missing = "missing";
    const GeneratePosters_All = "all";
    //Media Type
    const MediaType_Movie = "Movie";
    const MediaType_TvShow = "TvShow";
    const MediaType_TvEpisode = "TvEpisode";
    //
    const MetadataManagerAction_GeneratePosters = "GeneratePosters";
    const MetadataManagerAction_ReloadMetadata = "ReloadMetadata";
    const MetadataManagerAction_FetchMetadata = "FetchMetadata";
    const MetadataManagerAction_FetchPoster = "FetchPoster";
    const MetadataManagerAction_FetchAndGeneratePosters = "FetchAndGeneratePosters";
    const SecurityType_Public = "Public";
    const SecurityType_LoginRequired = "LoginRequired";

}

?>
