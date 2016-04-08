/**
 * @author Anakeen
 */

var notalone = true;
function completechoice(index, tattrid, tattrv, winfo) {
    var rvalue, i, attrid;
    for (i = 0; i < tattrid.length; i++) {
        if (tattrv[index][i]) {
            if (tattrv[index][i].substring(0, 1) != '?') {
                if (winfo.document.getElementById(tattrid[i]) && winfo.document.getElementById(tattrid[i]).type != "checkbox") {
                    attrid = tattrid[i];
                    rvalue = tattrv[index][i].replace(/\\n/g, '\n');
                    if (winfo.document.getElementById("mdocid_work" + attrid)) {
                        clearDocIdInputs(attrid, 'mdocid_isel_' + attrid, winfo.document.getElementById("ix_" + attrid), true);
                        ec_setIValue(winfo, winfo.document.getElementById(tattrid[i]), rvalue);
                        attrid = "mdocid_work" + attrid;
                    }
                    ec_setIValue(winfo, winfo.document.getElementById(attrid), rvalue);
                    winfo.document.getElementById(attrid).style.backgroundColor = '[COLOR_C8]';
                    sendEvent(winfo.document.getElementById(attrid), "change");
                    // This condition is for IE which does not send event in this case
                    if (isIE && winfo.document.getElementById(attrid).onchange) {
                        eval(winfo.document.getElementById(attrid).onchange);
                    }
                } else {
                    rvalue = tattrv[index][i].replace(/\\n/g, '\n');
                    if (!ec_setIValuePlus(winfo, tattrid[i], rvalue)) {
                        if ((tattrid[i].substring(0, 1) != '?') && (tattrid[i] != '')) {
                            if (notalone) alert('[TEXT:Attribute not found]' + '[' + tattrid[i] + ']' + winfo.name);
                        }
                    }
                }

            } else {
                if ((tattrv[index][i].length > 1) &&
                    ((winfo.document.getElementById(tattrid[i]).value == "") || (winfo.document.getElementById(tattrid[i]).value == " "))) {
                    rvalue = tattrv[index][i].substring(1).replace(/\\n/g, '\n');
                    winfo.document.getElementById(tattrid[i]).value = rvalue;
                    winfo.document.getElementById(tattrid[i]).style.backgroundColor = '[COLOR_C8]';
                    sendEvent(winfo.document.getElementById(tattrid[i]), "change");
                    // This condition is for IE which does not send event in this case
                    if (isIE && winfo.document.getElementById(tattrid[i]).onchange) {
                        eval(winfo.document.getElementById(tattrid[i]).onchange);
                    }
                }
            }
        }
    }
    winfo.disableReadAttribute();

    return;
}

var isNetscape = navigator.appName == "Netscape";


function completechoices() {
    var cvalues = [];
    var i = 0;
    var c = 0;
    for (i = 0; i < tattrid.length; i++) {
        cvalues[i] = "";
    }
    senum = document.getElementById('schoose');
    for (c = 0; c < senum.length; c++) {
        if (senum.options[c].selected) {
            index = senum.options[c].value;
            for (i = 0; i < tattrid.length; i++) {
                var currentAttr = winfo.document.getElementById(tattrid[i]);
                if (tattrv[index][i] != "") {
                    cvalues[i] += tattrv[index][i];
                    cvalues[i] += "\n";
                    currentAttr.style.backgroundColor = '[COLOR_C8]';
                }
            }
        }
    }
    for (i = 0; i < tattrid.length; i++) {
        if (cvalues[i][0] != '?'){
            // delete last CR
            winfo.document.getElementById(tattrid[i]).value = cvalues[i].substring(0, cvalues[i].length - 1);
        }
    }
    winfo.disableReadAttribute();
}

function autoClose() {
    // see if only one possibility
    if (tattrv.length == 1) {
        completechoice(0, tattrid, tattrv, winfo);
        setTimeout('self.close()', 200); // must be set in next event loop cause Mozilla crash sometimes
    }
}

function ec_setIValue(winfo, targetInput, value) {
    var hiddenTitle, newInput, isMultiple, values, elem, hasEmptyField, k;
    if (targetInput) {
        if (targetInput.tagName == "INPUT") {
            if ((targetInput.type == 'radio')) {
                if (value == '0') {
                    value = false;
                }
                targetInput.checked = value;

                if (value) {
                    winfo.changeCheckClasses(targetInput, false);
                }
            } else if ((targetInput.type == 'checkbox')) {
                //nothing to do
            } else if (targetInput.type == 'text' && winfo.document.getElementById("mdocid_work" + targetInput.id.substr(6))) {
                hiddenTitle = winfo.document.getElementById("hidden_" + targetInput.id);
                if (hiddenTitle) {
                    hiddenTitle.value = value;
                } else {
                    newInput = winfo.document.createElement('input');
                    newInput.setAttribute('type', 'hidden');
                    newInput.setAttribute('value', value);
                    newInput.setAttribute('id', "hidden_" + targetInput.id);
                    newInput.setAttribute('name', "hidden_" + targetInput.id);
                    targetInput.parentNode.appendChild(newInput);
                }
            } else {
                targetInput.value = value;
            }
        } else if (targetInput.tagName == "TEXTAREA") {
            targetInput.value = value;
        } else if (targetInput.tagName == "SELECT") {
            isMultiple = 'false';
            values = value;
            hasEmptyField = false;
            elem = winfo.document.getElementById("sp_" + targetInput.id);
            if (elem) {
                isMultiple = elem.parentNode.parentNode.parentNode.parentNode.getAttribute("multiple");
            }
            if (isMultiple != 'false') {
                values = value.split("\n");
            }
            for (k = 0; k < targetInput.options.length; k++) {
                if (targetInput.options[k].value == " ") {
                    hasEmptyField = true;
                }
                if (isMultiple != 'false') {
                    var valueToCheck = $.inArray(targetInput.options[k].value, values);
                    $.each(values, function (index, val) {
                        if (targetInput.options[k].value == val) {
                            targetInput.options[k].selected = true;
                        } else if (valueToCheck < 0) {
                            targetInput.options[k].selected = false;
                        }
                    });
                } else if (targetInput.options[k].value == value) {
                    targetInput.selectedIndex = k;
                }
            }
            if (values == " " && !hasEmptyField) {
                targetInput.add(new Option("[TEXT:Do choice]", values, true));
                targetInput.selectedIndex = targetInput.options.length - 1;
            }
        }
        ec_setIValuePlus(winfo, targetInput.id, value);
    }
}

function ec_setIValuePlus(winfo, iid, v) {
    var iid0 = iid + '_0';
    var i = 0;
    var ret = false;
    var isMultiple = false;
    var elem = winfo.document.getElementById("sp_" + iid);
    if (elem) {
        isMultiple = elem.parentNode.parentNode.parentNode.parentNode.getAttribute("multiple");
    }
    var oi = winfo.document.getElementById(iid0);
    while (oi) {
        if ((oi.type == 'radio') || (oi.type == 'checkbox')) {
            if (isMultiple != 'false') {
                var values = v.split("\n");
                var valueToCheck = $.inArray(oi.value, values);
                $.each(values, function (index, val) {
                    if (oi.value == val) {
                        oi.checked = true;
                        $(oi).trigger('click');
                        /**
                         * Must revaluate checked because click event change it.
                         * Can't prevent default click event because checkbox will not be checked in ie.
                         */
                        oi.checked = true;
                        ret = true;
                    } else if (oi.checked == true && valueToCheck < 0) {
                        oi.checked = false;
                        $(oi).trigger('click');
                        oi.checked = false;
                    }
                });
            } else if (oi.value == v) {
                oi.checked = true;
                $(oi).trigger('click');
                oi.checked = true;
                ret = true;
            }
        }
        i++;
        iid0 = iid + '_' + i.toString();
        oi = winfo.document.getElementById(iid0);
    }
    if (!ret && v == " ") {
        ret = true;
    }
    return ret;
}
