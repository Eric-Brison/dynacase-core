(function(window, document, $) {

    "use strict";

    var editimport, getWindowresult;

    editimport = function editimport() {
        var eurl;
        var famid;
        if (document.getElementById('classid')) {
            famid = $("#classid").val();
            eurl = '?app=GENERIC&action=GENERIC_EDITIMPORT&famid=' + famid;
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

    window.updateVisibilities = function updateVisibilities(canUpdate, maxImportSize) {
        if (canUpdate) {
            $("#fgimport").button("disable").css('font-weight', 'normal').val(window.i18n.limitReached+ " : " + maxImportSize);
            if (!$("#to").is(":visible")) {
                $("#fbgimport").find(".legend").click();
            }
        } else {
            $("#fgimport").button("enable").val(window.i18n.importDocument).css('font-weight', 'bold');
            $("#banalyze").css('font-weight', 'normal');
        }
    };


    $(document).ready(function() {
        $(".legend").on("click", function() {
            var $this = $(this);
            $this.toggleClass("collapsed");
            $this.parent().find(".content").toggle();
        });
        $('#mainform').attr("target", getWindowresult());
        $('input[type=submit], button, input[type=button], input[type=file]').button();
        $('.radio').buttonset();
        $('#ifile').on("change", function() {
            var fileExtension=$(this).val().split('.').pop().toLowerCase();
            if (fileExtension != "csv") {
                $("#csvOption input").attr("disabled", "disabled");
            } else {
                $("#csvOption input").removeAttr("disabled");
            }
            if (fileExtension == "zip") {
                 $("#archiveOption").show();
            } else {
                $("#archiveOption").hide();
            }
            $('#banalyze').button("enable");
        });
        $("#archiveOption").hide();
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
        $("#fgimport").on("click", function() {
            $('#action').val('FREEDOM_IMPORT');
            $('#analyze').val('N');
            $('#mainform').submit();
        });
    });
})(window, document, jQuery);