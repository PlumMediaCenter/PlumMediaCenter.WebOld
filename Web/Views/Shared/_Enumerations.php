<script type="text/javascript">
    var enumerations = {
        GeneratePosters: {
            None: "<?php echo Enumerations\GeneratePosters::None; ?>",
            Missing: "<?php echo Enumerations\GeneratePosters::Missing; ?>",
            All: "<?php echo Enumerations\GeneratePosters::All; ?>"
        },
        MediaType: {
            Movie: "<?php echo Enumerations\MediaType::Movie; ?>",
            TvShow: "<?php echo Enumerations\MediaType::TvShow; ?>",
            TvEpisode: "<?php echo Enumerations\MediaType::TvEpisode; ?>"
        },
        MetadataManagerAction: {
            GeneratePosters: "<?php echo Enumerations\MetadataManagerAction::GeneratePosters; ?>",
            ReloadMetadata: "<?php echo Enumerations\MetadataManagerAction::ReloadMetadata; ?>",
            FetchMetadata: "<?php echo Enumerations\MetadataManagerAction::FetchMetadata; ?>",
            FetchPoster: "<?php echo Enumerations\MetadataManagerAction::FetchPoster; ?>",
            FetchAndGeneratePosters: "<?php echo Enumerations\MetadataManagerAction::FetchAndGeneratePosters; ?>"
        },
        SecurityType: {
            Anonymous: "<?php echo Enumerations\SecurityType::Anonymous; ?>",
            LoginRequired: "<?php echo Enumerations\SecurityType::LoginRequired; ?>"
        },
        PlayType: {
            Single: "<?php echo Enumerations\PlayType::Single; ?>",
            TvShow: "<?php echo Enumerations\PlayType::TvShow; ?>",
            Playlist: "<?php echo Enumerations\PlayType::Playlist; ?>"
        }
    };
</script>