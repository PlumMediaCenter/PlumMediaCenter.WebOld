$(document).ready(function() {
    //bind add new source button click functionality
    $("#addNewSourceBtn").click(openAdd);

    //bind delete button functionality
    $(".deleteSource").click(function() {
        //get the location to delete
        var location = $(this).attr("data-location");
        deleteVideoSource(location);
    });

    //bind edit button functionality
    $(".editSource").click(function() {
        var $this = $(this);
        var location = $this.attr("data-location");
        var baseUrl = $this.attr("data-base-url");
        var mediaType = $this.attr("data-media-type");
        var securityType = $this.attr("data-security-type");
        openEdit(location, baseUrl, mediaType, securityType)
    });

    jQuery.validator.addMethod("endingSlash", function(value, element) {
        var c = value.charAt(value.length - 1);
        return c == "/" || c == "\\";
    }, "Must end with a slash.");

    //wire up the validation on the addEdit form
    var validator = $("#videoSourcesForm").validate({
        //only validate on form submission to save lag from the ajax checks
        onsubmit: true,
        onkeyup: false,
        onclick: false,
        onfocusout: false,
        errorPlacement: function(error, element) {
            if ($(element).is(":radio")) {
                //grab the last radio in the list
                var lastRadioButton = $("[name=" + $(element).attr("name") + "] + label").last();
                error.insertAfter(lastRadioButton);
                error.css({"margin-left": "5px"});
            } else {
                error.insertAfter(element);
            }
        },
        rules: {
            location: {
                required: true,
                endingSlash: true,
                remote: {
                    url: "api/PathExistsOnServer.php",
                    async: false,
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
                endingSlash: true
            },
            mediaType: {
                required: true,
            }
        },
        messages: {
            location: {
                remote: "*Please enter a valid path that is accessible by the server."
            },
            mediaType: {required: "<br/> This field is required"}
        }
    });

    function openAdd() {
        $("#addSourceBtn").show();
        $("#editSourceBtn").hide();
        openAddEdit();
    }

    function openEdit(location, baseUrl, mediaType, securityType) {
        $("#addSourceBtn").hide();
        $("#editSourceBtn").show();
        openAddEdit(location, baseUrl, mediaType, securityType);
    }

    function openAddEdit(location, baseUrl, mediaType, securityType) {
        //reset the validator form
        validator.resetForm();
        //$("#addSourceBtn, #editSourceBtn").disable();
        //if parameters were not provided, clear the inputs
        if (location === undefined && baseUrl === undefined && mediaType === undefined && securityType === undefined) {
            $("input[name=originalLocation]").val("");
            $("input[name=location]").val("");
            $("input[name=baseUrl]").val("");
            //$("input[name=securityType]").prop("checked", false);
            $("input[name=mediaType]").prop("checked", false);
        } else {
            $("input[name=originalLocation]").val(location);
            $("input[name=location]").val(location);
            $("input[name=baseUrl]").val(baseUrl);
            $("input[name=mediaType]").prop("checked", false).addBack("[value=" + mediaType + "]").prop("checked", true);
            $("input[name=securityType]").prop("checked", false).addBack("[value=" + securityType + "]").prop("checked", true);
        }
        //clear the message 
        $("#addEditMessage").html("");
        //show the add/edit window
        $("#newSourceModal").modal('show');
    }

    function deleteVideoSource(sourcePath) {
        if (confirm("Really delete this video source?")) {
            $.ajax({
                url: "api/DeleteVideoSource.php",
                dataType: "json",
                data: {sourcePath: sourcePath},
                complete: function(result) {
                    if (result.responseJSON != undefined && result.responseJSON == true) {
                        window.location.reload();
                    } else {
                        alert("Unable to delete video source");
                    }
                }
            });
        }
    }
});