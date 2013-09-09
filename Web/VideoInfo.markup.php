<script type="text/javascript" src="js/VideoInfo.js"></script>
<script type="text/javascript">
    var video = <?php echo $videoJson; ?>;
</script>
<h1><?php echo $video->title; ?></h1>

<img src="<?php echo $video->hdPosterUrl; ?>">
<br/>
<?php
include_once("code/functions.php");
printTvShowFileList($video);
?>

<div id="episodeInfo" class="shadow">
    <h1 id="title" style="text-align:center;"></h1>
    <span id="mpaa"></span>
    <span id="year"></span> 

    <div id="plot"></div>
</div>
