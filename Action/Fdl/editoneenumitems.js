$(function () {

    $("#editenumwidget").editenumitems({
        famid: $("#famid").val(),
        enumid: $("#enumid").val(),
        withlocale: true,
        title: "%s &gt; %e",
        helpMessage: "<div class='helpMessage'>[ZONE FDL:GETEDITENUMHELPMESSAGE]</div>",
        error: function (e, data) {
            var dialogModal = $("#dialogModal");
            dialogModal.html('<div class="message error">' + data.error + '</div>').dialog({
                modal: true,
                width:'auto',
                close: function () {
                    $(this).dialog("destroy");
                }
            });
        },
        confirm: function (e, data) {
            var dialogModal = $("#dialogModal");
            dialogModal.html('<div class="message confirm">' + data.msg + '</div>')
                .dialog({
                    modal: true,
                    width:'auto',
                    buttons: {
                        "[TEXT:EnumWidget:Reload data]": function () {
                            data.callback();
                            $(this).dialog("close");
                        },
                        "[TEXT:EnumWidget:Cancel]": function () {
                            $(this).dialog("close");
                        }
                    },
                    close: function () {
                        $(this).dialog("destroy");
                    }
                });
        },
        message: function (e, data) {
            var dialogModal = $("#dialogModal");
            dialogModal.html('<div class="message">' + data.msg + '</div>')
                .dialog({
                    modal: true,
                    width:'auto',
                    buttons: {
                        "OK": function () {
                            $(this).dialog("close");
                        }
                    },
                    close: function () {
                        $(this).dialog("destroy");
                    }
                });
            var mTitle=$(".message p").attr("data-title");
            if (mTitle) {
                dialogModal.dialog( "option", "title", mTitle );
            }
        },

        redraw: function () {
            fitHeight();
        }
    });

    function fitHeight() {
        var tbody = $(".dataTables_scrollBody");
        var tfoot = $('#newLine');
        var windowHeight = $(window).height();

        if (tbody.offset()) {
            var offY = tbody.offset().top;
            var footH = tfoot.height();
            tbody.height(windowHeight - offY - footH - 10);
        } else {
            setTimeout(fitHeight, 500);
        }
    }

});