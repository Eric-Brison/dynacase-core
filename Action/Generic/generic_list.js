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
            window.location.href=window.location.href;
        } else if (url) {
            window.location = url;
        }
    };

    $(document).on("ready", function () {
        $("#mainbarmenu").on("click", "[target=_overlay]", function () {
            var $this = $(this), href = $this.attr("url") || $this.attr("href");
            if (tryAllParent(window, "openIframeOverlay", href, reloadCurrentWindow) === false) {
                window.open(href);
            }
            return false;
        });
    });

}($, window));