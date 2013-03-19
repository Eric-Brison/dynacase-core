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
            $("#mainbarmenu").on("click", "[target=_overlay]", function () {
                var $this = $(this), href = $this.attr("url") || $this.attr("href");
                if (tryAllParent(window, "openIframeOverlay", href, reloadCurrentWindow) === false) {
                    window.open(href);
                }
                return false;
            });
            $("#selectsearches")

                .click(function () {
                    var menu = $(this).next().show().position({
                        my: "left top",
                        at: "left bottom",
                        of: this
                    });
                    $(document).one("click", function () {
                        menu.hide();
                    });
                    return false;
                })
               // .buttonset()
                .next()
                .hide()
                .menu();

            $("#searches").on("click","a",function(event) {
                $(this).parent().parent().find("a").attr("data-selected","0");

                $(this).attr("data-selected","1");
                if ($(this).attr("data-isreport")=="1") {
                    // direct display reports
                    SendSimpleSearch(event);
                   $(this).attr("data-selected","0");
                } else {
                    // display selected search
                    $("#selected-search-text").text($(this).text());
                    $("#selected-search").show();

                    focusInput();

                }
            });
           /* $('#searchkey').button();
            $('#searchgo').button({
                text: false,
                icons: {
                    primary: "ui-icon-search"
                }});*/
            $("#selected-search-text").text($('a[data-selected="1"]').text());
            if ($('a[data-selected="1"]').length > 0) {
                $("#selected-search").show();
            } else {
                $("#selected-search").hide();
            }
            $("#close-select-search").click(function() {
                $('a[data-selected="1"]').attr("data-selected","0");
                $("#selected-search").hide();

            });
            focusInput();
        }


    );

}($, window));