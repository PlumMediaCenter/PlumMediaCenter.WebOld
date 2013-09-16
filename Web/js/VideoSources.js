
$(document).ready(function() {
    //show/hide the public url input based on the security type selected    
    $("#securityType").change(setBaseUrlVisibility);
    //set the securityType value to be public by default
});

function setBaseUrlVisibility() {
    if ($("#securityType").val() == enumerations.SecurityType_Public) {
        $("#baseUrlRow").show();
    } else {
        $("#baseUrlRow").hide();
    }
}


function openAdd() {
    $("#addSourceBtn").show();
    $("#editSourceBtn").hide();
    openAddEdit();
}

function openEdit(baseFilePath, baseUrl, mediaType, securityType) {
    $("#addSourceBtn").hide();
    $("#editSourceBtn").show();
    openAddEdit(baseFilePath, baseUrl, mediaType, securityType);

}

function openAddEdit(baseFilePath, baseUrl, mediaType, securityType) {
    //if parameters were not provided, clear the inputs
    if (baseFilePath === undefined && baseUrl === undefined && mediaType === undefined && securityType === undefined) {
        $("input[name=originalLocation]").val("");
        $("input[name=location]").val("");
        $("input[name=baseUrl]").val("");
        $("#mediaType").val("");
        $("#securityType").val(enumerations.SecurityType_Public);
    } else {
        $("input[name=originalLocation]").val(baseFilePath);
        $("input[name=location]").val(baseFilePath);
        $("input[name=baseUrl]").val(baseUrl);
        $("#mediaType").val(mediaType);
        $("#securityType").val(securityType);
    }
    //clear the message 
    $("#addEditMessage").html("");
    setBaseUrlVisibility();
    //show the add/edit window
    $("#newSourceModal").modal();
}

function validateAddEdit() {
    if ($("input[name=location]").val().length == 0 || $("input[name=baseUrl]").val().length == 0) {
        $("#addEditMessage").html("*Please fill out all fields before clicking save");
        return false;
    }
    return true;
}


function deleteVideoSource(sourcePath) {
    if (confirm("Really delete this video source?")) {
        $("#deleteSource").val(sourcePath);
        $("form").submit();
    }
}