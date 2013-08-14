
<?php

include_once(dirname(__FILE__) . "/code/database/Queries.class.php");

class indexModel extends Model {

    public $videoCount;
    public $movieCount;
    public $tvShowCount;
    public $tvEpisodeCount;

    function __construct() {
        $counts = Queries::getVideoCounts();
        $this->videoCount = $counts->movieCount + $counts->tvEpisodeCount;
        $this->movieCount = $counts->movieCount;
        $this->tvShowCount = $counts->tvShowCount;
        $this->tvEpisodeCount = $counts->tvEpisodeCount;
    }

}

?>
