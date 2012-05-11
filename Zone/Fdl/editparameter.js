function sendParameterData(elem, id) {
    console.log("elem is == ", $(elem).parent(), this, $(elem).val());
    $(elem).parent().submit(function () {
        console.log("call to ajax");
        var multiple = $("#mdocid_isel_" + id);
        var value = [];
        if (multiple.length > 0) {
            $(multiple).find("option").each(function (index, elem) {
                value.push($(elem).attr("value"));
            });
        } else {
            var elem = $("#T" + id);
            console.log("element id is == ", elem);
            if (elem.length > 0) {
                elem.find("#tbody" + id).find("td.visibleAttribute").each(function (index, e) {
                    var attrid = $(e).attr("attrid");
                    var values = [];
                    $(e).find("input").each(function (i, el) {
                        values.push($(el).val());
                    });
                    $(e).find("select").each(function (i, el) {
                        values.push($(el).val());
                    });
                    value.push({
                        "attrid":attrid,
                        "value":values
                    });
                });
            } else {
                value = $("#" + id).val();
            }
        }
        console.log("id is == ", id, $(this), $("#fam_" + id).val());
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
                console.log("succes response :: ", rsp, textStatus, jqXHR);
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
    $(elem).parent().submit();
}

function sendParameterApplicationData(elem, id) {
    console.log("elem is == ", $(elem).parent(), this, $(elem).val());
    $(elem).parent().submit(function () {
        console.log("call to ajax");
        var value = $("#" + id).val();
        console.log("id is == ", id);
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
                console.log("succes response :: ", rsp, textStatus, jqXHR);
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
                console.log("error response :: ", error, data)
            }
        });
        return false;
    });
    $(elem).parent().submit();
}

function sendAllParameters() {
    var edit_family = $(".editfamilyparameter form");
    var edit_application = $(".editapplicationparameter form");
    edit_family.each(function (index, element) {
        var form = $(element);
        if (form.find(":submit").length > 0 || form.attr("data-on-change")) {
            console.log("subnit or on change found for edit family");
            return true;
        }
        sendParameterData(form.find('input[type="text"]'));
    });

    edit_application.each(function (index, element) {
        var form = $(element);
        if (form.find(":submit").length > 0 || form.attr("data-on-change")) {
            console.log("subnit or on change found for edit application");
            return true;
        }
        var select = form.find("select");
        if (select.length > 0) {
            sendParameterApplicationData(select);
        } else {
            sendParameterApplicationData(form.find('input[type="text"]'));
        }

    });
}