<script type="text/javascript" src="js/jquery.playlist.js"></script>
<link rel="stylesheet" href="css/jquery.playlist.css"/>
<script>
    $(function() {
<?php foreach ($playlists as $playlist) { ?>
            $("#playlistArea<?php echo $playlist; ?>").playlist({playlistName: "<?php echo $playlist; ?>"});
<?php } ?>
    });
</script>
<?php foreach ($playlists as $playlist) { ?>
    <div class="playlistArea" style="overflow:hidden;padding-bottom:20px;">
        <h1><?php echo $playlist; ?></h1>
        <div id="playlistArea<?php echo $playlist; ?>"></div>
    </div>
<?php } ?>
