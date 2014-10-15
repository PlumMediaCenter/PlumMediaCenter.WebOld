<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<h3>Summary</h3>
<div class="row">
    <div class="span2">Video Count: </div><div class="span2"><?php echo ($videoCount != null) ? $videoCount : "-"; ?></div>
</div>
<div class="row">
    <div class="span2">Movie Count:</div><div class="span2"><?php echo ($movieCount != null) ? $movieCount : "-"; ?></div>
</div>
<div class="row">
    <div class="span2">Tv Show Count:</div><div class="span2"><?php echo ($tvShowCount != null) ? $tvShowCount : "-"; ?></div>
</div>
<div class="row">
    <div class="span2">Tv Episode Count:</div><div class="span2"><?php echo ($tvEpisodeCount != null) ? $tvEpisodeCount : "-"; ?></div>
</div>
<script type="text/javascript">
    $("#homeNav").addClass("active");
</script>