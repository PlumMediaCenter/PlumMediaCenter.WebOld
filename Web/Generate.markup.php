<form id="formGenerate">
    <div class="row">
        <div class="span4">Movies Base Url (With trailing slash please)</div>
        <div class="span2"><input name="moviesUrl" type="text" style="width:300px;" value="http://localhost:8080/video/Movies/"/></div>
    </div>
    <div class="row">
        <div class="span4">Movies File Path (With trailing slash please)</div>
        <div class="span2"><input name="moviesFilePath" type="text" style="width:300px;" value="C:/Videos/Movies/"/></div>
    </div>
    <div class="row">
        <div class="span4">Tv Shows Base Url (With trailing slash please)</div>
        <div class="span2"><input name="tvShowsUrl" type="text" style="width:300px;" value="http://localhost:8080/video/Tv Shows/"/></div>
    </div>
    <div class="row">
        <div class="span4">Tv Shows File Path (With trailing slash please)</div>
        <div class="span2"><input name="tvShowsFilePath" type="text" style="width:300px;" value="C:/Videos/Tv Shows/"/></div>
    </div>
    <div class="row">
        <div class="span4">Generate Images:</div>
        <div class="span1">
            <input type="radio" name="generatePosters" checked="true" value="<?php echo Enumerations::GeneratePosters_None; ?>" id="GeneratePostersNone"/><label class="radio inline" for="GeneratePostersNone">None</label>
        </div>
        <div class="span2" style="text-align:center;">  
            <input type="radio" name="generatePosters" value="<?php echo Enumerations::GeneratePosters_Missing; ?>" id="GeneratePostersMissing"/><label class="radio inline" for="GeneratePostersMissing">Missing Only</label>    
        </div>
        <div class="span1">   
            <input type="radio" name="generatePosters" value="<?php echo Enumerations::GeneratePosters_All; ?>" id="GeneratePostersAll"/><label class="radio inline" for="GeneratePostersAll">All</label>
        </div>
    </div>
    <div class="row">
        <div class="span1">   Url: </div>
        <div class="span7"> <a href="" id="url" style="word-wrap:break-word; width: 100%;"></a></div>
    </div>

    Click this button to scan through your media collection and generate a json file containing information about all of your videos.
    <br/><input type = "submit" name="generate" value = "true"/>
</form>
<script type="text/javascript">
    $("input").click(generateUrl).keypress(generateUrl);
    function generateUrl() {
        var url = "Generate.php?" + $("#formGenerate").serialize() + "&generate=true";
        $("#url").attr("href", url).html(url);
    }
    //generate the url at page load
    generateUrl();
</script>