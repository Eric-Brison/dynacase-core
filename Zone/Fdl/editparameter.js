function sendParameterData(elem, id) {
    var form = $(elem).parents("form");
    if (!id) {
        id = form.parents("div.editfamilyparameter").attr("data-parameter");
    }
    form.off("submit");
    form.submit(function () {
        var extractValueFromField = function (e, value) {
            var attrid = $(e).attr("attrid");
            var values = [];
            $(e).find("input[type='text'],input[type='hidden'],select").each(function (i, el) {
                values.push($(el).val());
            });
            value.push({
                "attrid":attrid,
                "value":values
            });
            return value;
        };
        var multiple = $("#mdocid_isel_" + id);
        var value = [];
        if (multiple.length > 0) {
            $(multiple).find("option").each(function (index, elem) {
                value.push($(elem).attr("value"));
            });
        } else {
            var elem = $("#T" + id);
            if (elem.length > 0) {
                var tbodyelem = elem.find("#tbody" + id).find("td.visibleAttribute,td.hiddenAttribute");
                if (tbodyelem.length > 0) {
                    tbodyelem.each(function (index, e) {
                        value = extractValueFromField(e, value);
                    });
                } else {
                    elem.find("tfoot").find("td.visibleAttribute,td.hiddenAttribute").each(function (index, e) {
                        value = extractValueFromField(e, value);
                    });
                }
            } else {
                value = $("#" + id).val();
            }
        }
        $.ajax({
            url:'?app=FDL&action=MODFAMILYPARAMETER',
            type:'POST',
            "data":{
                "attrid":id,
                "value":value,
                "famid":$("#fam_" + id).val()
            },
            dataType:"xml",
            success:function (rsp, textStatus, jqXHR) {
                var $doc = $(rsp);
                var $status = $doc.find("status");
                var $data = $doc.find("data");
                $("body").trigger("MODPARAMETER", {
                    "success":$status.attr("code") ? true : false,
                    "error":$status.attr("warning"),
                    "data":{
                        "parameterid":$data.attr("parameterid"),
                        "modify":$data.attr("modify") ? true : false
                    }
                });

            },
            error:function (data, error) {
                $("body").trigger("MODPARAMETER", {
                    "success":false,
                    "error":error,
                    "data":{
                        "parameterid":id,
                        "modify":false,
                        "responseText":data.responseText ? data.responseText : data
                    }
                });
            }
        });
        return false;
    });
}

function sendParameterApplicationData(elem, id) {
    var form = $(elem).parents("form");
    if (!id) {
        id = form.parents("div.editapplicationparameter").attr("data-parameter");
    }
    form.submit(function () {
        var value = $("#" + id).val();
        $.ajax({
            url:'?app=FDL&action=MODAPPLICATIONPARAMETER',
            type:'POST',
            "data":{
                "name":id,
                "value":value,
                "appid":$("#app_" + id).val(),
                "type":$("#type_" + id).val()
            },
            dataType:"xml",
            success:function (rsp, textStatus, jqXHR) {
                var $doc = $(rsp);
                var $status = $doc.find("status");
                var $data = $doc.find("data");
                $("body").trigger("MODPARAMETER", {
                    "success":$status.attr("code") ? true : false,
                    "error":$status.attr("warning"),
                    "data":{
                        "parameterid":$data.attr("parameterid"),
                        "modify":$data.attr("modify") ? true : false
                    }
                });

            },
            error:function (data, error) {
                $("body").trigger("MODPARAMETER", {
                    "success":false,
                    "error":error,
                    "data":{
                        "parameterid":id,
                        "modify":false,
                        "responseText":data.responseText ? data.responseText : data
                    }
                });
            }
        });
        return false;
    });
    form.submit();
}

function sendAllParameters() {
    var edit_family = $(".editfamilyparameter");
    var edit_application = $(".editapplicationparameter");
    edit_family.each(function (index, element) {
        var form = $(element).find("form");
        if (form.find(":submit").length > 0 || form.attr("data-on-change")) {
            console.log("submit or on change found for edit family");
            return true;
        }
        sendParameterData(form.children()[0], $(element).attr("data-parameter"));
    });

    edit_application.each(function (index, element) {
        var form = $(element).find("form");
        if (form.find(":submit").length > 0 || form.attr("data-on-change")) {
            console.log("submit or on change found for edit application");
            return true;
        }
        sendParameterApplicationData(form.children()[0], $(element).attr("data-parameter"));

    });
}

function addOnChange() {
    var edit_family = $(".editfamilyparameter");
    var edit_application = $(".editapplicationparameter");
    edit_family.each(function (index, element) {
        var form = $(element).find("form");
        if (form.attr("data-on-change")) {
            form.find("input[type='text']").each(function (index, elem) {
                var f = function (e) {
                    e = e || event;
                    setTimeout("sendParameterData('#" + $(e.target).prop("id") + "');", 0)
                };
                if ("addEventListener" in elem) {
                    elem.addEventListener("change", f, false);
                }
                else if (elem.attachEvent) {
                    elem.attachEvent("onchange", f);
                }
            });
            form.find("select").each(function (index, elem) {
                var f = function (e) {
                    e = e || event;
                    setTimeout("sendParameterData('#" + $(e.target).prop("id") + "');", 0)
                };
                if ("addEventListener" in elem) {
                    elem.addEventListener("change", f, false);
                }
                else if (elem.attachEvent) {
                    elem.attachEvent("onchange", f);
                }
            });
        }
    });
    edit_application.each(function (index, element) {
        var form = $(element).find("form");
        if (form.attr("data-on-change")) {
            form.find("input[type='text']").each(function (index, elem) {
                var f = function (e) {
                    e = e || event;
                    setTimeout("sendParameterApplicationData('#" + $(e.target).prop("id") + "');", 0)
                };
                if ("addEventListener" in elem) {
                    elem.addEventListener("change", f, false);
                }
                else if (elem.attachEvent) {
                    elem.attachEvent("onchange", f);
                }
            });
            form.find("select").each(function (index, elem) {
                var f = function (e) {
                    e = e || event;
                    setTimeout("sendParameterApplicationData('#" + $(e.target).prop("id") + "');", 0)
                };
                if ("addEventListener" in elem) {
                    elem.addEventListener("change", f, false);
                }
                else if (elem.attachEvent) {
                    elem.attachEvent("onchange", f);
                }
            });
        }
    });
}

$(function () {
    addOnChange();
});
//add on change attribute to input field in new row of array
specAddtr = "addOnChange()";
//Call to server when line in array is deleted
specDeltr = "sendParameterData(parent)";