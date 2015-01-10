<?php

require_once(dirname(__FILE__) . '/../code/Enumerations.class.php');

$enumerations = (object) [
            'mediaType' => (object) [
                'movie' => Enumerations::MediaType_Movie,
                'show' => Enumerations::MediaType_TvShow,
                'episode' => Enumerations::MediaType_TvEpisode
            ],
            'securityType' => (object) [
                'public' => Enumerations::SecurityType_Public
            ]
];

header('Content-Type: application/json');
echo json_encode($enumerations);
?>
