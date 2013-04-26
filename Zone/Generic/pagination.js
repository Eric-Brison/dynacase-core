$(function () {

    var typeDef = {
        "nextpage": "ui-icon-circle-triangle-e",
        "prevpage": "ui-icon-circle-triangle-w",
        "lastpage": "ui-icon-circle-arrow-e",
        "firstpage": "ui-icon-circle-arrow-w"
    };
    $(".pagination-button").each(function () {
        $(this).button({
            "text": false,
            "icons": {
                "primary": typeDef[$(this).attr("data-type")]
            }
        });
    });
});
