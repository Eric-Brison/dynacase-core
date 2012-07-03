$(document).ready(function () {

    $('#current').dataTable({
        "sDom":'<"top"ft<"bottom"><"clear">',
        bJQueryUI:true,
        bSort:false,
        bScrollInfinite:false,
        bAutoWidth :false,
        bScrollCollapse:($('tr', '#current').length < 10),
        sScrollY:"200px",
        iDisplayLength:500,
        fnRowCallback:function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            $(nRow).removeClass('odd even');

        },
        fnInitComplete:function (oSettings, json) {
            if (!document.getElementById('history')) _histoui.redraw();
            var ins = $('input[aria-controls="current"]')[0];
            ins.setAttribute('placeholder', $('#current')[0].getAttribute('data-searchText'));
            $('tr.comment[level="1"]').hide();

        },
        oLanguage:{
            "sSearch":""
        },
                aoColumnDefs:[
                    { "sWidth":"15px", "aTargets":[ 2, 3,4 ] }
                ]
    });
    $('#history').find('tr.comment').hide();
    $('#history').dataTable({
        "sDom":'<"top"ft<"bottom"><"clear">',
        bJQueryUI:true,
        bSort:false,
        bScrollInfinite:false,
        bAutoWidth :false,
        bScrollCollapse:false,
        sScrollY:"200px",
        iDisplayLength:5000,
        fnRowCallback:function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            $(nRow).removeClass('odd even');

        },
        fnInitComplete:function (oSettings, json) {
            _histoui.redraw();
            var ins = $('input[aria-controls="history"]')[0];
            if (ins) ins.setAttribute('placeholder', $('#history')[0].getAttribute('data-searchText'));
            $('tr.comment[level="1"]').hide();
        },
        oLanguage:{
            "sSearch":""
        },
        aoColumnDefs:[
            { "sWidth":"15px", "aTargets":[ 2, 3,4 ] }
        ]
    });
    $("table").on("click", "td.revision", function (event) {
        var sel = 'tr[rev="' + $.trim($(this).text()) + '"]';
        var isVisible = null;
        $(sel).each(function () {
                if ($(this).is(':visible')) {
                    $(this).hide();
                    isVisible = false;
                } else {
                    $(this).show();
                    isVisible = true;
                    //$(this).addClass('ui-icon ui-icon-circlesmall-minus');
                }
            }
        );
        var spanIcon = $(this).find('span')[0];

        if (isVisible) {
            $(spanIcon).addClass('ui-icon-circlesmall-minus');
            $(spanIcon).removeClass('ui-icon-circlesmall-plus');

        } else {
            $(spanIcon).addClass('ui-icon-circlesmall-plus');
            $(spanIcon).removeClass('ui-icon-circlesmall-minus');

        }
        _histoui.hideLevel();
    });

    $("#history, #current").on("click", "td.state", function (event) {
        var tr = $(this).parent('tr')[0];
        var docid = tr.getAttribute('docid');
        subwindow('200px', '300px', '_blank', '?app=FDL&action=FDL_CARD&id=' + docid);

    });

    $("#history, #current").on("click", 'input[name="diff"]', function (event) {

        var docid = $(this).val();
        var checked = $('input:checked[name="diff"]', '#history, #current');
        if (checked.length > 1) {
            $('input:not(:checked)[name="diff"]', '#history, #current').attr('disabled', true);
            subwindow('200px', '300px', 'diff', '?app=FDL&action=DIFFDOC&id1=' + $(checked[0]).val() + '&id2=' + $(checked[1]).val())
        } else {
            $('input:disabled[name="diff"]', '#history, #current').attr('disabled', false);
        }

    });

    $("div.filterLevel").on("click", 'input[type="checkbox"]', function (event) {


        var checked = $('input[type="checkbox"]', 'div.filterLevel');
        for (var i =0 ;i <checked.length;i++) {
            if (checked[i].checked) {
                $('tr.comment[level="'+checked[i].value+'"]').show();
                $(checked[i]).prev('label').addClass('checked');
            }
            else  {
                $('tr.comment[level="'+checked[i].value+'"]').hide();
                $(checked[i]).prev('label').removeClass('checked');
            }
        }

    });
    $("#allrev").on("click", function (event) {
        var spanIcon = $(this).find('span')[0];
        if (this.getAttribute('isCollapsed') == 1) {
            $('#history tr.comment').hide();
            this.setAttribute('isCollapsed', 0);
            $('#history td.revision span.ui-icon').addClass('ui-icon-circlesmall-plus');
            $('#history td.revision span.ui-icon').removeClass('ui-icon-circlesmall-minus');
            $(spanIcon).addClass('ui-icon-circlesmall-plus');
            $(spanIcon).removeClass('ui-icon-circlesmall-minus');
        } else {
            $('#history tr.comment').show();
            this.setAttribute('isCollapsed', 1);
            $('#history td.revision span.ui-icon').addClass('ui-icon-circlesmall-minus');
            $('#history td.revision span.ui-icon').removeClass('ui-icon-circlesmall-plus');
            $(spanIcon).addClass('ui-icon-circlesmall-minus');
            $(spanIcon).removeClass('ui-icon-circlesmall-plus');
            _histoui.hideLevel();
        }

    });

});

var _histoui = {
    _histoWidth:0,
    _histoHeight:0
};
_histoui.redraw = function () {

    var wh = $(window).height();
    var ww = $(window).width();
    var y = 0;
    if (ww != this._histoWidth) {
        $('#current').dataTable().fnDraw();
        if ($('#history').length) $('#history').dataTable().fnDraw();
        this._histoWidth = ww;
    }
    if (wh != this._histoHeight) {
        var thead = $('#history_wrapper table.dataTable')[0];
        if (thead) {
            y = $(thead).offset().top + $(thead).height() + 5;
            $('div.dataTables_scrollBody:eq(1)').css('height', (wh - y - 0) + 'px');
        } else {
            thead = $('#current_wrapper table.dataTable')[0];
            if (thead) {
                y = $(thead).offset().top + $(thead).height() + 5;

                $('div.dataTables_scrollBody:eq(0)').css('height', (wh - y - 0) + 'px');
            }
        }
        this._histoHeight = wh;
    }
    //
};
_histoui.hideLevel = function () {

    var checked = $('input:not(:checked)[type="checkbox"]', 'div.filterLevel');
            for (var i =0 ;i <checked.length;i++) {
                 $('tr.comment[level="'+checked[i].value+'"]').hide();
            }

    //
};


$(window).resize(function () {
    _histoui.redraw();
});


