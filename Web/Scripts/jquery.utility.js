(function($, undefined) {
    /**
     * Disables an input 
     */
    $.fn.disable = function() {
        $(this).attr('disabled', 'disabled').addClass("disabled");
    };

    $.fn.enable = function() {
        $(this).removeAttr('disabled').removeClass("disabled");
    };

}(jQuery, undefined));