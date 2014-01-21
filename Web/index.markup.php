<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<h3>Summary</h3>
<div style="width:300px;">
<div class="row" >
    <div class="col-md-6">Video Count: </div><div class="col-md-6"><?php echo ($videoCount != null) ? $videoCount : "-"; ?></div>
</div>
<div class="row">
    <div class="col-md-6">Movie Count:</div><div class="col-md-6"><?php echo ($movieCount != null) ? $movieCount : "-"; ?></div>
</div>
<div class="row">
    <div class="col-md-6">Tv Show Count:</div><div class="col-md-6"><?php echo ($tvShowCount != null) ? $tvShowCount : "-"; ?></div>
</div>
<div class="row">
    <div class="col-md-6">Tv Episode Count:</div><div class="col-md-6"><?php echo ($tvEpisodeCount != null) ? $tvEpisodeCount : "-"; ?></div>
</div>
<script type="text/javascript">
    $("#homeNav").addClass("active");
</script>
</div>