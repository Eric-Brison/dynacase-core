$(function () {

    $("#editenumwidget").editenumitems({
        famid: $("#famid").val(),
        enumid: $("#enumid").val(),
        withlocale: true,
        error: function(e, data) {
            var dialogModal = $("#dialogModal");
            dialogModal.html('<div class="message error">'+data.error+'</div>').dialog({
               modal:true
            });
            console.log("envent recieve is == ", data);
        }
    });

    function fitHeight () {
       var tbody= $(".dataTables_scrollBody");
       var tfoot=$('#newLine');
       var windowHeight = $(window).height();
       // console.log('resize', windowHeight,tbody.offset(), footH) ;
       if (tbody.offset()) {
           var offY=tbody.offset().top;
           var footH=tfoot.height();
           tbody.height(windowHeight - offY - footH - 10);
       } else {
           setTimeout(fitHeight, 500);
       }
    }

     $(window).on(
            'resize',
            fitHeight
     );

    fitHeight();

});