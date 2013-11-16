<script type="text/javascript" src="js/jquery.playlist.js"></script>
<link rel="stylesheet" href="css/jquery.playlist.css"/>
<script>
    $(function() {
<?php foreach ($playlists as $key => $playlist) { ?>
            $("#playlistArea<?php echo $key; ?>").playlist({playlistName: "<?php echo $playlist; ?>"});
<?php } ?>
    });
</script>
<?php foreach ($playlists as $key => $playlist) { ?>
    <div class="playlistArea" style="overflow:hidden;padding-bottom:20px;">
        <h1><?php echo $playlist; ?></h1>
        <div id="playlistArea<?php echo $key; ?>"></div>
    </div>
<?php } ?>
