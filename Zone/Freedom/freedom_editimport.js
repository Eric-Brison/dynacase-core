(function(window, document, $) {

    "use strict";

    var editimport, getWindowresult;

    editimport = function editimport() {
        var eurl;
        var famid;
        if (document.getElementById('classid')) {
            famid = $("#classid").val();
            eurl = '[CORE_STANDURL]&app=GENERIC&action=GENERIC_EDITIMPORT&famid=' + famid;
            window.open(eurl, '_blank');
        }
    };

    getWindowresult = function getWindowresult() {
        if (window.parent && window.parent.document) {
            var frame = window.parent.document.getElementById("resultw");
            if (frame) {
                return frame.getAttribute("name");
            }
        }
        return "defaultAnalysis";
    };


    $(document).ready(function() {
        $(".legend").on("click", function() {
            var $this = $(this);
            $this.toggleClass("collapsed");
            $this.parent().find(".content").toggle();
        });
        $('#mainform').attr("target", getWindowresult());
        $('input[type=submit], button, input[type=button]').button();
        $('.radio').buttonset();
        $('#ifile').on("change", function() {
            if ($(this).val().split('.').pop() != "csv") {
                $("#csvOption input").attr("disabled", "disabled");
            } else {
                $("#csvOption input").removeAttr("disabled");
            }
            $('#banalyze').button("enable");
        });

        $('#classid').on("change", function() {
            if ($(this).val() != "0") {
                $(this).parent().find('input').button("enable");
            } else {
                $(this).parent().find('input').button("disable");
            }
        });
        $("#banalyze").on("click", function() {
            $('#action').val('FREEDOM_IMPORT');
            $('#analyze').val('Y');
            $('#mainform').submit();
        });
        $("fgimport").on("click", function() {
            $('#action').val('FREEDOM_IMPORT');
            $('#analyze').val('N');
            $('#mainform').submit();
        });
    });
})(window, document, jQuery);
