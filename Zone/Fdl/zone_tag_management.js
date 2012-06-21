/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

$(function () {
    function split(val) {
        return val.split(/[,;\s*]\s*/);
    }

    function extractLast(term) {
        return split(term).pop();
    }

    function displayNewData(data) {
        console.log("data are", data);
        var docTags = $("#documentTags");
        var newTagsDisplay = $("#newTagsToAdd");
        var editMode = $("#editMode").val();
        console.log("edit mode is == ", editMode, $("#editMode").val());
        if (!$.isPlainObject(data)) {
            docTags.html(data);
            return false;
        }
        if (data.error) {
            docTags.html(data.error);
        } else {
            var newTags = "";
            var lastval = "";
            $.each(data.data, function (index, value) {
                if (index != 0) {
                    newTags += ",";
                    if (editMode) {
                        newTags += '<button id="deleteTag_' + lastval + '" class="deleteTagButton">-</button>';
                    }
                    newTags += " ";
                }
                newTags += '<a href="#">' + value + '</a>';
                lastval = value;
            });
            if (lastval && editMode) {
                newTags += '<button id="deleteTag_' + lastval + '" class="deleteTagButton">-</button>'
            }
            newTagsDisplay.val("");
            newTagsDisplay.css("display", "none");
            docTags.html(newTags);
            $(".deleteTagButton").on("click", function (event) {
                console.log("click on minus");
                var tagName = this.id.substr("deleteTag_".length);
                var docid = $("#docidForTags").val();
                var newTagsDisplay = $("#newTagsToAdd");

                console.log("tagname in minus is == ", tagName);
                $.ajax({
                    "type":"POST",
                    "url":"?app=FDL&action=TAG_MANAGEMENT&id=" + docid + "&type=delete",
                    "data":{
                        "tags":tagName
                    },
                    "success":displayNewData
                });
                return false;
            });
        }
        return false;
    }

    console.log("FORM ADD TAGS is == ", $("#formAddTags"));

    $(".deleteTagButton").on("click", function (event) {
        console.log("click on minus");
        var tagName = this.id.substr("deleteTag_".length);
        var docid = $("#docidForTags").val();
        var newTagsDisplay = $("#newTagsToAdd");

        console.log("tagname in minus is == ", tagName);
        $.ajax({
            "type":"POST",
            "url":"?app=FDL&action=TAG_MANAGEMENT&id=" + docid + "&type=delete",
            "data":{
                "tags":tagName
            },
            "success":displayNewData
        });
        return false;
    });

    $("#addTags").on("click", function () {
        var newTagsDisplay = $("#newTagsToAdd");
        var docid = $("#docidForTags").val();

        console.log("SUBMIT val is == ", newTagsDisplay);
        if (newTagsDisplay.val() == "") {
            var display = newTagsDisplay.css("display");
            if (display == "inline") {
                newTagsDisplay.css("display", "none");
            } else {
                newTagsDisplay.css("display", "inline");
                newTagsDisplay.focus();
                $.ajax({
                    "type":"GET",
                    "url":"?app=FDL&action=TAG_MANAGEMENT&id=" + docid + "&type=getAll",
                    "success":function (data) {
                        var newTagsDisplay = $("#newTagsToAdd");
                        var docTags = $("#documentTags");
                        if (!$.isPlainObject(data)) {
                            docTags.html(data);
                            return false;
                        }
                        if (data.error) {
                            docTags.html(data.error);
                        } else {
                            var availableTags = data.data;
                            console.log("availabletags are == ", availableTags);
                            newTagsDisplay.on("keydown",function (event) {
                                if (event.keyCode === $.ui.keyCode.TAB &&
                                    $(this).data("autocomplete").menu.active) {
                                    event.preventDefault();
                                }
                            }).autocomplete({
                                    minLength:0,
                                    position:{
                                        "my":"left bottom",
                                        "at":"left top"
                                    },
                                    source:function (request, response) {
                                        console.log("request term == ", request.term);
                                        response($.ui.autocomplete.filter(
                                            availableTags, extractLast(request.term)));
                                    },
                                    focus:function () {
                                        // prevent value inserted on focus
                                        return false;
                                    },
                                    select:function (event, ui) {
                                        var terms = split(this.value);
                                        console.log("TEMS ARE == ", terms);
                                        // remove the current input
                                        terms.pop();
                                        // add the selected item
                                        terms.push(ui.item.value);
                                        // add placeholder to get the comma-and-space at the end
                                        terms.push("");
                                        this.value = terms.join(", ");
                                        return false;
                                    }
                                });
                        }
                    }
                });
            }
            return false;
        }
        console.log("submit diplay val -- docid", newTagsDisplay.val(), docid);

        $.ajax({
            "type":"POST",
            "url":"?app=FDL&action=TAG_MANAGEMENT&id=" + docid + "&type=add",
            "data":{
                "tags":newTagsDisplay.val()
            },
            "success":displayNewData
        });
        return false;
    });
});