<script type="text/javascript" src="<?php urlContent('~/Scripts/jquery.video.js'); ?>"></script>
<script type="text/javascript" src="<?php urlContent('~/Scripts/jquery.genreVideoList.js'); ?>"></script>
<script type = "text/javascript" >
    $(document).ready(function() {
        var $genreBox = $("#genreBox");
        plumapi.getGenreList(function(names) {
            $.each(names, function(idx, val) {
                //add a new div for this genre
                $genreBox.append("<div id='" + val + "'></div>");
                //get the new div for this genre        
                var genreVideoList = $genreBox.find("#" + val);
                //create a new genreVideoList jquery widget object to handle the genres
                genreVideoList.genreVideoList({genreName: val});
                $genreBox.append("<div class='clearfix'></div>");
            });
        });
    });
</script>

<div id="genreBox"></div>