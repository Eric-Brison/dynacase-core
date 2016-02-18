
$(document).ready(function () {
    var $expertAnchor=$('<a />');
    var expertMode=(window.location.href.indexOf("&mode=expert") > 0);

    if (expertMode) {
        $expertAnchor.text("[TEXT:Switch Normal Mode]");
    } else {
        $expertAnchor.text("[TEXT:Switch Expert Mode]");
    }
    $("div.barmenu").append($expertAnchor);

    $expertAnchor.on("click", function() {
       if (expertMode) {
           window.location.href=window.location.href.replace("&mode=expert","");
       } else {
            window.location.href+="&mode=expert";
       }
    });


});