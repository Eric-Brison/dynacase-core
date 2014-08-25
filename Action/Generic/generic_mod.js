
function setConstraint(info) {
    if (!info)
        return;
    var first = true;
    window.parent.undisplayConstraint();
    var inp=null;
   
    for ( var i in info) {
        if (info[i].index == null) {
            // mono value
            inp = window.parent.document.getElementById(info[i].id);
            if (!inp) inp = window.parent.document.getElementById('F'+info[i].id);
            if (!inp) inp = window.parent.document.getElementById('T'+info[i].id);
            if (first) {
                focusConstraint(inp);
                // first=false;
            }
            window.parent.viewConstraint(inp, info[i]);

        } else {
            var linp = window.parent.getInputsByName('_' + info[i].id,
                    window.parent.document.getElementById('T' + info[i].pid));
            for ( var k = 0; k < linp.length; k++) {
                if (info[i].index == k) {
                     inp = linp[k];
                    if (first) {
                        // first=false;
                        focusConstraint(inp);
                    }
                    window.parent.viewConstraint(inp, info[i]);
                }
            }
        }
    }
    for ( var j in info) {
        if (!info[j].displayed) {
            tryToDisplayStructureAttribute(info[j]);
            window.parent.displayWarningMsg(info[j].prefix+' : '+info[j].err);
            
        }
    }
}

function tryToDisplayStructureAttribute(cinfo) {
    var elt=window.parent.document.getElementById('F'+cinfo.id);
    if (!elt) elt=window.parent.document.getElementById('T'+cinfo.id);
    if (elt) elt.className+=' invalid';
}

function focusConstraint(inp) {
    if (inp) {
        var p = inp.parentNode;
        while (p && ((p.tagName != 'FIELDSET') || (!p.getAttribute('name'))))
            p = p.parentNode;
        if (p) {
            var name = p.getAttribute('name');
            if (name) {
                var tab = window.parent.document.getElementById('TAB'
                        + name.substr(3));
                tab.onmousedown.apply(tab, []);
                window.parent.$(tab).addClass('invalid');

            }
        }
        try {
            //inp.focus();
        } catch (exception) {

        }
    }
}



function updateOpenerLink(data) {
    if (window.parent != null && window.parent.opener != null) {
        var windowParentOpener=window.parent.opener;
        var docIdInput, docIdButton;

        docIdInput = windowParentOpener.document.getElementById('ilink_'+data.attrid);
        if (docIdInput) {
            docIdInput.value=data.title;
        }

        // special for doc multiple
        docIdInput=windowParentOpener.document.getElementById('mdocid_work'+data.attrid);
        if (docIdInput) {
            docIdInput.value=data.id;
            windowParentOpener.addmdocs('_'+data.attrid);
        } else {
            // normal case
            docIdInput=windowParentOpener.document.getElementById(data.attrid);
            if (docIdInput) {
                docIdInput.value=data.id;
                if (data.recallhelper) {
                    docIdButton = windowParentOpener.document.getElementById('ic_ilink_'+data.attrid);
                }
                if (docIdButton) {
                    docIdButton.onclick.call(docIdButton);
                }
            }
        }
        windowParentOpener.disableReadAttribute();
    }
}
