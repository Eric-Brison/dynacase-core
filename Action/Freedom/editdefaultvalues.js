$(document).on("ready", function () {

    var hashAid = window.location.href.split('#')[1];
    var $defVal = $(".defval");
    var $defaultValues=$(".default-values");

    $defaultValues.dataTable({
        bPaginate: false,
        "aaSorting": [],
        "bSort": false,
        bAutoWidth:false,
        "sDom": '<"top"ft<"bottom"><"clear">',
        oLanguage: {
            "sSearch": ""
        }
    });

    $(".dataTables_filter input").attr("placeholder", function () {
        return $(this).closest(".top").find(".default-values").data("filter");
    } );

    $defVal.on("click", "a", function (event) {
        event.stopPropagation();
    });

    $defVal.on("click", function () {

        var aid = $(this).data("attrid");
        var famid = $(".default-values").data("famid");
        var url = "?app=FREEDOM&action=EDITONEDEFAULTVALUE&famid=" + famid + '&attrid=' + aid;
        var dialogFrame = $("#defvalDialog");
        if (dialogFrame.length > 0) {
            dialogFrame.remove();
        }


        dialogFrame = $('<div id="defvalDialog" >' + '<iframe ' +
        'src="' + url + '"' +
        ' class="default-frame"  frameborder="0"  allowtransparency="yes"></iframe></div>').appendTo('body');

        dialogFrame.dialog({
            autoOpen: false,
            modal: true,
            draggable: true,
            resizable: true,
            height: $(window).height() * 0.8,
            width: $(window).width() * 0.8,
            title: '<img src="' + $(".icon").attr("src") + '"> ' + $(this).find(".attribute-label").html() +
            '<span class="attribute-id">"' + $(this).find(".attribute-id").html() + '"</span>'
        });
        dialogFrame.dialog("open");

    });
    $defVal.tipsy({
        html: true,
        title: function () {
            return $("h1").data("title").replace("{{aid}}", $(this).data("attrid"));
        }
    });
    if (hashAid) {
        $("#" + hashAid).addClass("selected");
    }


});