
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

var notalone=true;
function completechoice(index,tattrid,tattrv,winfo) {
    var rvalue;
    for (var i=0; i< tattrid.length; i++) {
        if (tattrv[index][i]) {
            if  (tattrv[index][i].substring(0,1) != '?')  {
                if (winfo.document.getElementById(tattrid[i]) && winfo.document.getElementById(tattrid[i]).type != "checkbox") {
                    var attrid = tattrid[i];
                    rvalue = tattrv[index][i].replace(/\\n/g,'\n');
                    if (winfo.document.getElementById("mdocid_work"+attrid)) {
                        clearDocIdInputs(attrid, 'mdocid_isel_'+attrid, winfo.document.getElementById("ix_"+attrid), true);
                        ec_setIValue(winfo,winfo.document.getElementById(tattrid[i]),rvalue);
                        attrid = "mdocid_work"+attrid;
                    }
                    ec_setIValue(winfo,winfo.document.getElementById(attrid),rvalue);
                    winfo.document.getElementById(attrid).style.backgroundColor='[COLOR_C8]';
                    sendEvent(winfo.document.getElementById(attrid),"change");
                    // This condition is for IE which does not send event in this case
                    if(isIE && winfo.document.getElementById(attrid).onchange){
                        eval(winfo.document.getElementById(attrid).onchange);
                    }
                } else {
                    rvalue = tattrv[index][i].replace(/\\n/g,'\n');
                    if (! ec_setIValuePlus(winfo,tattrid[i],rvalue)) {
                        if ((tattrid[i].substring(0,1) !='?') && (tattrid[i]!='')) {
                            if (notalone) alert('[TEXT:Attribute not found]'+'['+tattrid[i]+']'+winfo.name);
                        }
                    }
                }

      } else {
	if ((tattrv[index][i].length > 1) &&
	    ((winfo.document.getElementById(tattrid[i]).value == "") || (winfo.document.getElementById(tattrid[i]).value == " "))) {
	  rvalue = tattrv[index][i].substring(1).replace(/\\n/g,'\n');
	  winfo.document.getElementById(tattrid[i]).value = rvalue;
	  winfo.document.getElementById(tattrid[i]).style.backgroundColor='[COLOR_C8]';
	  sendEvent(winfo.document.getElementById(tattrid[i]),"change");
	  // This condition is for IE which does not send event in this case
	  if(isIE && winfo.document.getElementById(tattrid[i]).onchange){
          eval(winfo.document.getElementById(tattrid[i]).onchange);
      }
	}
      }
      }
  }
  winfo.disableReadAttribute();

  return;


}

var isNetscape = navigator.appName=="Netscape";





function completechoices() {
    var cvalues = new Array();
    var i=0;
    var c=0;
    for (i=0; i< tattrid.length; i++) {
      cvalues[i] ="";
    }
    senum = document.getElementById('schoose');
    for (c=0; c< senum.length; c++) {
      if (senum.options[c].selected) {
	index= senum.options[c].value;
	for (i=0; i< tattrid.length; i++) {
	  with (winfo.document.getElementById(tattrid[i])) {
	    if (tattrv[index][i] != "") {
	      cvalues[i] += tattrv[index][i];
	       cvalues[i] += "\n";
	      style.backgroundColor='[COLOR_C8]';
	    }
	    //       style.fontWeight='bold';
	  }
	}
      }
    }
    for (i=0; i< tattrid.length; i++) {
      if (cvalues[i][0] != '?')
	// delete last CR
	winfo.document.getElementById(tattrid[i]).value = cvalues[i].substring(0,cvalues[i].length-1);
    }
    winfo.disableReadAttribute();
}

function autoClose() {
  // see if only one possibility
  if (tattrv.length == 1) {
     completechoice(0,tattrid,tattrv,winfo);
     setTimeout('self.close()',200); // must be set in next event loop cause Mozilla crash sometimes
  }



}

function ec_setIValue(winfo,i,v) {
    if (i) {
        if (i.tagName == "INPUT") {
            if ((i.type=='radio')) {
                var oi=i.checked;
                if (v=='0') v=false;
                i.checked=v;

                if (v && (i.type=='radio')) winfo.changeCheckClasses(i,false);
                if (v && (i.type=='checkbox')) {
                    if (oi != v)  i.onclick.apply(i,[]);
                    //	  winfo.changeCheckBoolClasses(i,false);
                }
            } else if ((i.type=='checkbox')) {

            } else if (i.type=='text' && winfo.document.getElementById("mdocid_work"+i.id.substr(6))) {
                var hiddenTitle = winfo.document.getElementById("hidden_"+ i.id);
                if (hiddenTitle) {
                    hiddenTitle.value = v;
                } else {
                    i.parentNode.innerHTML += '<input type="hidden" value="'+v+'" id="hidden_'+i.id+'" name="hidden_'+i.id+'">';
                }
            } else {
                i.value=v;
            }
        }
        else if (i.tagName == "TEXTAREA")  i.value=v;
        else  if (i.tagName == "SELECT") {
            var isMultiple = 'false';
            var values = v;
            var elem = winfo.document.getElementById("sp_"+ i.id);
            var hasEmptyField = false;
            if (elem) {
                isMultiple = elem.parentNode.parentNode.parentNode.parentNode.getAttribute("multiple");
            }
            if (isMultiple != 'false') {
                values = v.split("\n");
            }
            for (var k=0;k<i.options.length;k++) {
                if (i.options[k].value == " ") {
                    hasEmptyField = true;
                }
                if (isMultiple != 'false') {
                    var valueToCheck = $.inArray(i.options[k].value, values);
                    $.each(values, function(index, val) {
                        if (i.options[k].value == val) i.options[k].selected=true;
                        else if(valueToCheck < 0) i.options[k].selected=false;
                    });
                } else if (i.options[k].value == v) i.selectedIndex=k;
            }
            if (values == " " && !hasEmptyField) {
                i.add(new Option("[TEXT:Do choice]", values, true));
                i.selectedIndex= i.options.length - 1;
            }
        }
        ec_setIValuePlus(winfo,i.id,v);
    }

}

function ec_setIValuePlus(winfo,iid,v) {
    var iid0= iid+'_0';
    var i=0;
    var oi=winfo.document.getElementById(iid0);
    var ret=false;
    var isMultiple = false;
    var elem = winfo.document.getElementById("sp_"+iid);
    if (elem) {
        isMultiple = elem.parentNode.parentNode.parentNode.parentNode.getAttribute("multiple");
    }
    while (oi) {
        if (oi) {
            if ((oi.type=='radio')||(oi.type=='checkbox')) {
                if (isMultiple != 'false') {
                    var values = v.split("\n");
                    var valueToCheck = $.inArray(oi.value, values);
                    $.each(values, function(index, val) {
                        if (oi.value==val) {
                            oi.checked=true;
                            oi.onclick.apply(oi,[]);
                            ret=true;
                        } else if (oi.checked == true && valueToCheck < 0){
                            oi.checked = false;
                            oi.onclick.apply(oi,[]);
                        }
                    });
                } else if (oi.value==v) {
                    oi.checked=true;
                    oi.onclick.apply(oi,[]);
                    ret=true;
                }
            }
        }
        i++;
        iid0=iid+'_'+i.toString();
        oi=winfo.document.getElementById(iid0);
    }
    if (!ret && v == " ") {
        ret = true;
    }
    return ret;
}
