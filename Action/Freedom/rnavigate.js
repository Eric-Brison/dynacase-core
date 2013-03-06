(function (window, $) {

    var nbColumn = 0, nbColumnMax = 4;

    function handleAjaxRequest(requestObject, success, fail) {
        requestObject.pipe(
            function (response) {
                if (response.success) {
                    return (response);
                }
                return ($.Deferred().reject(response));
            },
            function (response) {
                return ({
                    success : false,
                    result :  null,
                    error :   "Unexpected error: " + response.status + " " + response.statusText
                });
            }
        ).then(success, fail);
    }

    function logError(err) {
        err = err.error || err;
        if (window.console && $.isFunction(window.console.log)) {
            window.console.log(err);
        } else {
            window.alert(err);
        }
    }

    function formatElement(currentElement) {
        var $currentElement, $currentImg, $a;
        $currentElement = $('<li class="css-document-wrapper"></li>').data("initid", currentElement.initid);
        $currentElement.attr("title", currentElement.attributeLabel || "");
        $a = $('<a class="css-document-link"></a>').text(currentElement.title || "").attr("href", currentElement.url);
        $currentElement.append($('<div class="css-document-open-next js-document-open-next"></div>').append($a));
        $currentImg = $('<img height="16px" class="css-document-icon js-document-icon"/>').attr("src", currentElement.iconsrc || "");
        $currentImg.data("document-url", currentElement.url);
        $currentElement.prepend($currentImg);
        return $currentElement;
    }

    function formatElementsList(data) {
        var i, $wrapper = $('<div class="css-links-wrapper"></div>'), $title, currentDocument,
            relations, $ul, noneElement = true;

        $(".css-links-wrapper").each(function () {
            var $this = $(this);
            if (parseInt($this.data("nbcolumn"), 10) >= nbColumn) {
                $this.empty().remove();
            }
        });
        $wrapper.data("nbcolumn", nbColumn);

        currentDocument = data.currentDocument || {};

        $title = $('<div class="css-column-title">' + (currentDocument.title || '') + '</div>')
            .attr("title", currentDocument.title || "")
            .data("id", currentDocument.id || "");
        $wrapper.append($title);

        $wrapper.append('<span class="css-list-title">' + window.rnavigate.i18n["referenced from"] + '</span>');

        relations = data.relationsFrom || {};

        //noinspection JSJQueryEfficiency
        $ul = $('<ul class="css-list-wrapper"></ul>');
        for (i in relations) {
            if (relations.hasOwnProperty(i)) {
                $ul.append(formatElement(relations[i]));
                noneElement = false;
            }
        }
        if (noneElement) {
            //noinspection JSJQueryEfficiency
            $wrapper.append($('<span class="css-list-none-element"></span>').text(window.rnavigate.i18n["noone document"]));
        } else {
            $wrapper.append($ul);
        }
        noneElement = true;

        relations = data.relationsTo || {};
        $wrapper.append('<span class="css-list-title">' + window.rnavigate.i18n.referenced + '</span>');

        //noinspection JSJQueryEfficiency
        $ul = $('<ul class="css-list-wrapper"></ul>');
        for (i in relations) {
            if (relations.hasOwnProperty(i)) {
                $ul.append(formatElement(relations[i]));
                noneElement = false;
            }
        }

        if (noneElement) {
            //noinspection JSJQueryEfficiency
            $wrapper.append($('<span class="css-list-none-element"></span>').text(window.rnavigate.i18n["noone document"]));
        } else {
            $wrapper.append($ul);
        }

        $("body").append($wrapper);

    }

    $(document).ready(function () {
        var $body = $("body"), rnavigate;
        $body.on("click", ".js-document-open-next", function (event) {
            var $this = $(this), $parent = $(this).parent();
            $this.closest(".css-links-wrapper").find(".css-document-wrapper").removeClass("css-document-selected");
            event.preventDefault();
            handleAjaxRequest($.getJSON("?app=FREEDOM&action=RNAVIGATE_JSON", { id : $parent.data("initid") }),
                function (result) {
                    var data = result.data || {};
                    $parent.addClass("css-document-selected");
                    nbColumn = parseInt($parent.closest(".css-links-wrapper").data("nbcolumn"), 10) + 1;
                    formatElementsList(data);
                },
                logError);
            return false;
        });
        $body.on("click", ".js-document-icon", function () {
            subwindow(300, 400, '_blank', $(this).data("document-url"));
        });
        rnavigate = window.rnavigate || {};
        formatElementsList((rnavigate.initial_data || {}));
    });



}(window, $));