function KeySendSimpleSearch(e) {
    var keyCode;

    if (window.event) keyCode=window.event.keyCode;
    else keyCode = e.which;

    if (keyCode==13) {
        SendSimpleSearch(e)
    }
}


function sendSort(onefamOrigin, dirid,catg, famid, order) {
    var url='?app=GENERIC&action=GENERIC_USORT&onefam='+onefamOrigin+'&famid='+famid+'&aorder='+order;

    var reTab=/tab=(\d+)/
    var r=reTab.exec(window.location.href);
    if (r && r[1]) {
        url += '&tab='+r[1];
    }
    url += '&catg='+catg;
    url += '&dirid='+dirid;
    window.location.href=url;

}

function sendSimpleSearchP(event, famid, onefamOrigin, dirId, folderId, pds) {
    var isreport = false;
    var isparam = false;


    var fldid = folderId;
    var key = document.getElementById('searchkey').value;
    var dmainid = document.getElementById('cellmain');

    var selectedsearch=$('a[data-selected="1"]');
    if (selectedsearch) {

        fldid = selectedsearch.attr("data-searchid");

        isreport = (selectedsearch.attr("data-isreport") == '1');
        isparam = (selectedsearch.attr("data-isparam") == '1');
    }
    if (isreport) {
        subwindow(300, 400, 'finfo'+famid, '?app=FDL&action=FDL_CARD&dochead=Y&latest=Y&id=' + fldid);
    } else {
        if (isparam) {
            if (fldid != parseInt('[catg]', 10)) {

                key = false;
                document.getElementById('searchkey').value = '';
            } else {
                if (key == '') pds = '';
            }
        }

        if (dmainid) {
            dmainid.innerHTML = '<img  src="Images/loading.gif" style="background-color:#FFFFFF;border:groove black 2px;padding:4px;-moz-border-radius:4px">';
            dmainid.style.textAlign = 'center';
        }
        if ((fldid > 0) && (!key)) document.location.replace('?app=GENERIC&action=GENERIC_TAB&onefam='+onefamOrigin+'&tab=0&clearkey=Y&famid='+famid+'&catg=' + fldid + '&dirid=' + fldid + pds);
        else if (key)  document.location.replace('?app=GENERIC&onefam='+onefamOrigin+'&action=GENERIC_SEARCH&famid='+famid+'&dirid='+dirId+'&catg=' + fldid + pds + '&keyword=' + key );
        else document.location.replace('?app=GENERIC&action=GENERIC_TAB&tab=0&onefam='+onefamOrigin+'&famid='+famid+'&catg=-1&clearkey=Y');
    }

}


var prevselid;
// view select document
function vselect(th) {
    if (prevselid)  document.getElementById(prevselid).setAttribute("selected",0);
    th.setAttribute("selected",1);
    prevselid = th.id;
}
function resizeBodyHeigth() {
    var foot=$('#searchFooter');
    var cBody=$('#innermain');
    var fh=foot.outerHeight();
    var y=cBody.position().top;
    var bh=document.documentElement.clientHeight;
    var delta=0;
    var newHeight=bh-fh-y-delta;
    cBody.height(newHeight);

}
$(window).on("load", function () {
    if (!isIE) {
        $('body').css('visibility','visible').hide().show();
    }
});
function vedit(e,id,famid) {
    if (!e) e=window.event;

    if (e.ctrlKey) {
        subwindow(400,500,'fedit'+id,'?app=GENERIC&action=GENERIC_EDIT&latest=Y&id='+id);
    } else {
        subwindow(400,500,'finfo'+famid,'?app=GENERIC&action=GENERIC_EDIT&&latest=Y&id='+id);
    }
}

(function ($, window) {

    var tryAllParent, reloadCurrentWindow;
    tryAllParent = function tryAllParent(window, functionStringName) {
        var i, length, args = Array.prototype.slice.call(arguments),
            currentWindow = args.shift(),
            functionName = args.shift(),
            parentList = [],
            generateParentList = function (parent) {
                parentList.unshift(parent);
                if (parent !== parent.top && parent.parent) {
                    generateParentList(parent.parent);
                }
            };
        generateParentList(currentWindow);
        for (i = 0, length = parentList.length; i < length; i += 1) {
            if ($.isFunction(parentList[i][functionName])) {
                return parentList[i][functionName].apply(parent, args);
            }
        }
        return false;
    };

    reloadCurrentWindow = function reloadCurrentWindow(url) {
        if (url === "reload") {
            window.location.href = window.location.href;
        } else if (url) {
            window.location = url;
        }
    };


    focusInput= function focusInput(event) {
        $("#searchkey").focus();
                            // set cursor at the end when reset value
                            var iVal=document.getElementById('searchkey');
                            var pVal=iVal.value;
                            iVal.value='';
                            iVal.value=pVal;
           };

    $(document).on("ready", function () {
            $("#id-search-help").on("click", function() {
                    window.parent.parent.displayOverlay('aide', this);
                    return false;
                });

            $("#mainbarmenu").on("click", "[target=_overlay]", function () {
                var $this = $(this), href = $this.attr("url") || $this.attr("href");
                if (tryAllParent(window, "openIframeOverlay", href, reloadCurrentWindow) === false) {
                    window.open(href);
                }
                return false;
            });
            $("#selectsearches")

                .click(function () {
                    var menu = $("#searches").show().position({
                        my: "left top",
                        at: "left bottom",
                        of: this
                    });
                    $(document).one("click", function () {
                        menu.hide();
                    });
                    return false;
                });
               // .buttonset()

            $("#searches").hide().menu().on("click","a",function(event) {
                $(this).parent().parent().find("a").attr("data-selected","0");

                $(this).attr("data-selected","1");
                if ($(this).attr("data-isreport")=="1") {
                    // direct display reports
                    SendSimpleSearch(event);
                    $(this).attr("data-selected","0");
                } else {
                    // display selected search
                    $("#selected-search-text").text($(this).text());
                    $("#selected-search").css('background-color',$(this).css('background-color'));
                    $("#selected-search").show();
                    document.getElementById('searchkey').value='';
                    focusInput();
                    // display search
                    SendSimpleSearch(event);
                }
            });
           /* $('#searchkey').button();
            $('#searchgo').button({
                text: false,
                icons: {
                    primary: "ui-icon-search"
                }});*/
            var aselected=$('a[data-selected="1"]').first();
            $("#selected-search-text").text(aselected.text());
            if (aselected.css('background-color') != 'transparent') {
                $("#selected-search").css('background-color',(aselected.css('background-color')));
            }
            if ($('a[data-selected="1"]').length > 0) {
                $("#selected-search").show();
            } else {
                $("#selected-search").hide();
            }
            $("#close-select-search").click(function() {
                $('a[data-selected="1"]').attr("data-selected","0");
                $("#selected-search").hide();
                focusInput();
            });
            focusInput();
        }


    );

}($, window));