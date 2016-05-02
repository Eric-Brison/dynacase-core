function pollingExport(exportId) {
    $.ajax({
        url: '?',
        dataType: 'json',
        type: "GET",
        data: {exportId: exportId, statusOnly: 1, app: 'FDL', action: 'EXPORTFLD'},
        success: function (data) {
            var bExport = $('#bExport');
            bExport.val(data.status);
            if (stopExportPolling < 1) {
                if (!data.end) setTimeout(function () {
                    pollingExport(exportId);
                }, 500);
                else {
                    window.setTimeout(function() {
                        bExport.attr('disabled', false);
                        bExport.val("[TEXT:Redo export]");
                    }, 2000);
                }
            } else {
                window.setTimeout(function() {
                    bExport.attr('disabled', false);
                    bExport.val("[TEXT:Redo export]");
                }, 2000);
            }
            if (data.warnings && data.warnings.length > 0) {
                var $warning=$("<div/>").addClass("warning");
                var $button=$("<button/>").addClass("close").html("&times;");
                var $p;
                $warning.append($button);
                for (var i=0;i<data.warnings.length;i++) {
                    if (i===0) {
                        $p=$("<h3/>");
                    } else {
                        $p=$("<p/>");
                    }
                    $p.text(data.warnings[i]);
                    $warning.append($p);
                }

                $("body").append($warning);

                $button.on("click", function () {
                    $(".warning").hide();
                });
            } else {
                $(".warning").hide();
            }
        }
    });
}
stopExportPolling = 0;
$(document).ready(function () {

    $('#outputfile').load(function () {
        stopExportPolling++;
        $('#outputfile').css("height", "100px")
    });
    $('#exportForm').submit(function () {
        stopExportPolling = 0;
        setTimeout(function () {
            $('#bExport').attr('disabled', true);
        }, 20);
        setTimeout(function () {
            pollingExport($('#iExportId').val());
        }, 200);

    });
    $("select[name=eformat]").on("change", function testexport() {
            var mode = $(this).val();
            var f = this.form;
            if (mode == 'X' || mode == 'Y') {
                //f.action.value = 'EXPORTXMLFLD';
                for (var i in f.code.options) {
                    if (f.code.options[i].value == 'utf8') f.code.options[i].selected = true;
                    else  f.code.options[i].selected = false;
                }
                f.code.disabled = true;
                f.wprof.disabled = true;
                f.wproftype.disabled = true;
                //f.wident.disabled=true;
                f.wcolumn.disabled = true;
                f.wprof.className = 'disable';
                f.wproftype.className = 'disable';
                //f.wident.className='disable';
                f.wcolumn.className = 'disable';
            } else {
                //  f.action.value = 'EXPORTFLD';
                f.code.disabled = false;
                f.wprof.disabled = false;
                f.wproftype.disabled = false;
                f.wident.disabled = false;
                f.wcolumn.disabled = false;
                f.wprof.className = '';
                f.wproftype.className = '';
                f.wident.className = '';
                f.wcolumn.className = '';
            }
            var formatVal = $(this).val();
            if (formatVal === "X" || formatVal === "Y") {
                $('.csv--option').attr("disabled", "disabled").addClass("disable");
                $("select[name=csv-enclosure]").addClass("disable");
            } else {
                $('.csv--option').removeAttr("disabled").removeClass("disable");
                $("select[name=csv-enclosure]").trigger("change").removeClass("disable");
            }
        }
    );

    $("select[name=wproftype]").css("visibility", "hidden");
    $("select[name=wprof]").on("change", function () {
        var wprof = $(this).val();
        if (wprof === "Y") {
            $("select[name=wproftype]").css("visibility", "");
        } else {
            $("select[name=wproftype]").css("visibility", "hidden");
        }
    });
    $("select[name=csv-enclosure]").on("change", function () {
        var enclosureVal = $(this).val();
        if (enclosureVal === "other") {
            $(".other--enclosure").css("visibility", "visible").focus();
            $('#bExport').attr("disabled", "disabled");
        } else {
            $(".other--enclosure").css("visibility", "");
            $('#bExport').removeAttr("disabled");
        }
        if (enclosureVal == "") {
            $("select[name=csv-separator]").val(';').attr("disabled", "disabled");
            $(".other--separator").css("visibility", "");
        } else {
            $("select[name=csv-separator]").removeAttr("disabled");
        }
    });

    $(".other--enclosure").on("change", function () {
        var otherVal = $(this).val().substr(0, 1);

        $('#bExport').removeAttr("disabled");
        $("select[name=csv-enclosure]").append(new Option('[TEXT:csv-custom : ]' + otherVal, otherVal, true, true));
    });

    $("select[name=csv-separator]").on("change", function () {
        var separatorVal = $(this).val();
        if (separatorVal === "other") {
            $(".other--separator").css("visibility", "visible").focus();
            $('#bExport').attr("disabled", "disabled");
        } else {
            $(".other--separator").css("visibility", "");
            $('#bExport').removeAttr("disabled");
        }


    });

    $(".other--separator").on("change", function () {
        var otherVal = $(this).val().substr(0, 1);
        $('#bExport').removeAttr("disabled");
        $("select[name=csv-separator]").append(new Option('[TEXT:csv-custom : ]' + otherVal, otherVal, true, true));
        $(this).css("visibility", "");
        $(this).val('');
    });



    $("select[name=csv-enclosure]").trigger("change");
});