<script type="text/javascript" src="js/jquery.playlist.js"></script>
<link rel="stylesheet" href="css/jquery.playlist.css"/>

<?php foreach ($playlists as $key => $playlist) { ?>
    <div playlist-name='<?php echo $playlist; ?>' class="playlistArea" style="overflow:hidden;padding-bottom:20px;">
        <h1 style="display:inline;"><a href="<?php echo "Play.php?playlistName=$playlist"; ?>" title="Start Playing Playlist"><?php echo $playlist; ?></a></h1>
        <a class="btn" style="display:inline;" onclick="if (confirm('Really delete this playlist?') == true) {
                        deletePlaylist('<?php echo $playlist; ?>');
                    }" >Delete</a>
        <div id ="playlistArea<?php echo $key; ?>"></div>
    </div>
<?php } ?>
<script>
    $(function() {
<?php foreach ($playlists as $key => $playlist) { ?>
            $("#playlistArea<?php echo $key; ?>").playlist({playlistName: "<?php echo $playlist; ?>"});
<?php } ?>
    });

    function deletePlaylist(playlistName) {
        $.getJSON("api/DeletePlaylist.php", {playlistName: playlistName}, function(success) {
            if (success == true) {
                $("[playlist-name='" + playlistName + "']").remove();
            }
        });
    }
</script>