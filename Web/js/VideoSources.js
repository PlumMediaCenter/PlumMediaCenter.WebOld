
$(document).ready(function() {
//wire up the validation on the addEdit form
    $("#videoSourcesForm").validate({
        errorPlacement: function(error, element) {
            if ($(element).is(":radio")) {
                //grab the last radio in the list
                var lastRadioButton = $("[name=" + $(element).attr("name") + "]").last();
                error.insertAfter(lastRadioButton);
            } else {
                error.insertAfter(element);
            }
        },
        rules: {
            location: {
                required: true,
                remote: {
                    url: "ajax/PathExistsOnServer.php",
                    data: {
                        path: function() {
                            return $("#location").val();
                        }
                    }
                }
            },
            securityType: {required: true},
            baseUrl: {
                required: true,
                url2: true},
            mediaType: {
                required: true,
            }
        },
        messages: {
            location: {
                remote: "*Please enter a valid path that is accessible by the server."
            }
        }
    });
});
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
//$("#addSourceBtn, #editSourceBtn").disable();
//if parameters were not provided, clear the inputs
    if (baseFilePath === undefined && baseUrl === undefined && mediaType === undefined && securityType === undefined) {
        $("input[name=originalLocation]").val("");
        $("input[name=location]").val("");
        $("input[name=baseUrl]").val("");
        $("input[name=mediaType]").prop("checked", false);
        $("input[name=securityType]").prop("checked", false);
    } else {
        $("input[name=originalLocation]").val(baseFilePath);
        $("input[name=location]").val(baseFilePath);
        $("input[name=baseUrl]").val(baseUrl);
        $("input[name=mediaType]").prop("checked", false).addBack("[value=" + mediaType + "]").prop("checked", true);
        $("input[name=securityType]").prop("checked", false).addBack("[value=" + securityType + "]").prop("checked", true);
    }
//clear the message 
    $("#addEditMessage").html("");
    //show the add/edit window
    $("#newSourceModal").modal();
}

function validateAddEdit() {
//    var allValid = true;
//    if ($("input[name=location]").val().length == 0 || $("input[name=baseUrl]").val().length == 0) {
//        $("#addEditMessage").html("*Please fill out all fields before clicking save");
//        allValid = false;
//        return false;
//    }
}


function deleteVideoSource(sourcePath) {
    if (confirm("Really delete this video source?")) {
        $("#deleteSource").val(sourcePath);
        $("form").submit();
    }
}