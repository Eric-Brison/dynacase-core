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
                $("#csvOption input").prop("disabled", true);
            } else {
                $("#csvOption input").prop("disabled", false);
            }
            if (fileExtension == "zip") {
                 $("#archiveOption").show();
            } else {
                $("#archiveOption").hide();
            }
            $('#banalyze').prop("disabled", false).button("enable");
            $('#bgimport').prop("disabled", true).button("disable");
            $('#fgimport').prop("disabled", true).button("disable");
        });
        $("#archiveOption").hide();
        $('#classid').on("change", function() {
            if ($(this).val() != "0") {
                $(this).parent().find('input').button("enable");
                $(this).parent().find('input').prop("disabled",false);
            } else {
                $(this).parent().find('input').button("disable");
                $(this).parent().find('input').prop("disabled",true);
            }
        });
        $("#banalyze").on("click", function() {

            window.parent.document.getElementById("resultw").contentDocument.body.innerHTML=$("#waitAnalyze").html();

            $('#action').val('FREEDOM_IMPORT');
            $('#analyze').val('Y');

            window.setTimeout(function () {
                $('#mainform').submit();
            }, 1);
        });
        $("#fgimport").on("click", function() {
            window.parent.document.getElementById("resultw").contentDocument.body.innerHTML=$("#waitImport").html();
            $('#action').val('FREEDOM_IMPORT');
            $('#analyze').val('N');

            window.setTimeout(function () {
                $('#mainform').submit();
            }, 1);
        });
    });
})(window, document, jQuery);