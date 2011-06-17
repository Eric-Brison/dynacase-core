
function setConstraint(info) {
    if (!info)
        return;
    var first = true;
    undisplayConstraint();
    var inp=null;
   
    for ( var i in info) {
        if (info[i].index == null) {
            // mono value
             inp = window.parent.document.getElementById(info[i].id);
            if (first) {
                focusConstraint(inp);
                // first=false;
            }
            viewContraint(inp, info[i]);

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
                    viewContraint(inp, info[i]);
                }
            }
        }
    }
    for ( var j in info) {
        if (!info[j].displayed) {
            window.parent.displayWarningMsg(info[j].err);
        }
    }

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
                tab.className = 'invalid';

            }
        }
        try {
            inp.focus();
        } catch (exception) {

        }
    }
}
function viewContraint(inp, info) {
    if (inp) {
        var realinp = inp;
        if ((inp.style.display == 'none') || (inp.type == 'hidden')) {
            var oinp = inp.parentNode.firstChild;
            while (oinp
                    && ((oinp.tagName != 'INPUT' && oinp.tagName != 'SELECT') || (oinp.type == 'hidden' || (oinp.style && oinp.style.display == 'none')))) {
                oinp = oinp.nextSibling;
            }
            if (oinp && oinp.tagName == 'INPUT')
                inp = oinp;
            else {
                if (oinp && oinp.tagName == 'SELECT')
                    inp = oinp;
                else
                    inp = inp.parentNode;
            }
        }
        inp.className += ' invalid';
        var ntr = window.parent.document.createElement("div");
        ntr.className = 'constraint';
        if (inp.id) {
            var xy = window.parent.getAnchorPosition(inp.id);
            ntr.style.top = xy.y;
            ntr.style.left = xy.x;
        }
        var sp = window.parent.document.createElement("span");
        sp.innerHTML = info.err;
        info.displayed = true;
        ntr.appendChild(sp);
        addEvent(sp, "click", function() {
            ntr.style.display = 'none';
            try {
                inp.focus();
            } catch (exception) {
            }
        });
        inp.parentNode.insertBefore(ntr, inp);

        if (info.sug && (info.sug.length > 0)) {
            var s = window.parent.document.createElement("select");
            s.options[s.options.length] = new Option('[TEXT:Suggestion]', '');
            addEvent(s, "change", function() {
                realinp.value = s.options[s.selectedIndex].value;
                realinp.onchange.apply(realinp, []);
            });
            for ( var i in info.sug) {
                s.options[s.options.length] = new Option(info.sug[i],
                        info.sug[i]);
            }
            ntr.appendChild(s);
        }
    }
}
function undisplayConstraint() {
    var ldiv = window.parent.document.getElementsByTagName('div');
    var i=0;
    for (  i = 0; i < ldiv.length; i++) { // >
        if (ldiv[i].className == 'constraint') {
            ldiv[i].parentNode.removeChild(ldiv[i]);
            i--;
        }
    }
    var itag = new Array('input', 'textarea', 'select');
    for (var t in itag) {
        var ti = window.parent.document.getElementsByTagName(itag[t]);

        for (  i = 0; i < ti.length; i++) {
            if (ti[i].className.indexOf('invalid') > 0)
                ti[i].className = ti[i].className.replace('invalid', '');
        }
    }
}

function updateOpenerLink(data) {
    if (window.parent != null && window.parent.opener != null) {
        var wop=window.parent.opener;
        var inp=wop.document.getElementById(data.attrid);
        if (inp) inp.value=data.id;
        inp=wop.document.getElementById('ilink_'+data.attrid);
        if (inp) inp.value=data.title;
        
        wop.disableReadAttribute();
    }
}
