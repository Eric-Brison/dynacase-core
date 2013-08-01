(function ($, window, document) {
    "use strict";
    if (!("console" in window)) {
            window.console = {'log': function (s) {
                return s;
            }};
        }

    var resizeHeights;

    resizeHeights = function resizeHeights() {
            var  $window = $(window), windowHeight, $content = $("#iframelist");

            windowHeight = $window.height();

        //console.log('resize', h,contentOffset,windowHeight - $content.offset().top - 10 );
            var newH=windowHeight - $content.offset().top - 10;
             $("iframe.enumframe:visible").height(newH);
            $("div.enumlistblock").height(newH);
        };


     /**
       * resize items on window resize
        */
        $(window).on(
            'resize',
             resizeHeights
        );






    $(document).ready(function () {

      $('#enumlist').menu();
        $('a.item').on("click", function () {
            var o=$(this);
            var url=o.attr('url');
            var iframeId=o.attr('data-iframeid');
            $("iframe.enumframe").hide();
            if (! iframeId) {
                o.attr('data-iframeid', 'i'+o.attr('data-enumid'));
                iframeId=o.attr('data-iframeid');
                 $('<iframe class="enumframe" id="' + iframeId + '" src="' + url + '"></iframe>')

                    .appendTo('#iframelist');
            } else {
                $('#'+iframeId).show();
            }

            resizeHeights();
            $('a.item').removeClass('ui-state-highlight');
            o.addClass('ui-state-highlight enum-loaded ui-state-active');
        });

        resizeHeights();

    });

}($, window, document));
