<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<h3>Summary</h3>
<b>Video Count:</b>
<?php echo ($videoCount != null) ? $videoCount : "-"; ?>
<br/>
<b>Movie Count:</b>
<?php echo ($movieCount != null) ? $movieCount : "-"; ?>
<br/>
<b>Tv Show Count:</b>
<?php echo ($tvShowCount != null) ? $tvShowCount : "-"; ?>
<br/>
<b>Tv Episode Count:</b>
<?php echo ($tvEpisodeCount != null) ? $tvEpisodeCount : "-"; ?>
<br/>
<script type="text/javascript">
    $("#homeNav").addClass("active");
</script>