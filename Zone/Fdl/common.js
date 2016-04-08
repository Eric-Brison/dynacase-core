
/**
 * @author Anakeen
 */

var isNetscape = navigator.appName=="Netscape";

function viewornot(id, openOther) {
    var o=document.getElementById(id);

    var toggleOpen = (o && o.style.display == 'none') ? true : false;
    o.setAttribute('opened', (toggleOpen) ? 'true' : 'false');
    o.style.display = (toggleOpen) ? '' : 'none';

    if (openOther instanceof Array) {
        for (var i = 0; i < openOther.length; i++) {
            o = document.getElementById(openOther[i]);
            if (o) {
                o.setAttribute('opened', (toggleOpen) ? 'true' : 'false');
            }
        }
    }
}

function visibleornot(id) {
  var o=document.getElementById(id);
  if (o) {
    if (o.style.visibility=='hidden') o.style.visibility='visible';
    else o.style.visibility='hidden';
  }
}
 
// serach element in array
// return index found (-1 if not)
function array_search(elt,ar) {
  for (var i=0;i<ar.length;i++) {
    if (ar[i]==elt) return i;
  }
  return -1;
}

// only for mozilla
function moz_unfade(dvid) {
  var f;
  var dv=document.getElementById(dvid);  
  if (dv && dv.style.MozOpacity) {
    f=parseFloat(dv.style.MozOpacity);
    if (f < 1) {
      dv.style.MozOpacity=f+0.02;
      
      setTimeout('moz_unfade(\''+dvid+'\')',10);
    } 
  }
}




// return value in computed style
// o : the node HTML Object
// attribute name (marginLeft, top, backgroundColor)
function getCssStyle(o,a) {
  var result = 0;
  var sa='';
  var j=0;
  
  if (document.defaultView) {
    var style = document.defaultView;
    var cssDecl = style.getComputedStyle(o, "");
    for (var i=0;i<a.length;i++) {
	  if (a[i]<='Z') {
	    sa+='-';
	    sa+=a[i].toLowerCase();
	  } else {
	    sa+=a[i];
	  }

    } 
    result = cssDecl.getPropertyValue(sa);
  } else if (o.currentStyle) {
    result = o.currentStyle[a];
  } 
  return result;
}
    	
function copy_clip(meintext)
{

 if (window.clipboardData) 
   {
   
   // the IE-manier
   window.clipboardData.setData("Text", meintext);
   alert('copy :'+meintext);
   // waarschijnlijk niet de beste manier om Moz/NS te detecteren;
   // het is mij echter onbekend vanaf welke versie dit precies werkt:
   }
   else if (window.netscape) 
   { 
   
   // dit is belangrijk maar staat nergens duidelijk vermeld:
   // you have to sign the code to enable this, or see notes below 
   netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
   
   // maak een interface naar het clipboard
   var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
   if (!clip) return false;
   
   // maak een transferable
   var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
   if (!trans) return false;
   
   // specificeer wat voor soort data we op willen halen; text in dit geval
   trans.addDataFlavor('text/unicode');
   
   // om de data uit de transferable te halen hebben we 2 nieuwe objecten nodig   om het in op te slaan
   var str = new Object();
   var len = new Object();
   
   var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
   
   var copytext=meintext;
   
   str.data=copytext;
   
   trans.setTransferData("text/unicode",str,copytext.length*2);
   
   var clipid=Components.interfaces.nsIClipboard;
   
   if (!clip) return false;
   
   clip.setData(trans,null,clipid.kGlobalClipboard);
   
   }
   alert("Following info was copied to your clipboard:\n\n" + meintext);
   return false;
}

function shiftPushed(event) {  
  if (!event) event=window.event;
  return event.shiftKey;
}
function altPushed(event) {    
  if (!event) event=window.event;
  if (event) return event.altKey;
  return false;
}
function ctrlPushed(event) {  
  if (!event) event=window.event;
  if (event) return event.ctrlKey;
  return false;
}


function trackMenuKey(event) {
  var intKeyCode;

  if (!event) event=window.event;
  if (!event) return true;
  if (isNetscape) {
    intKeyCode = event.keyCode;
    altKey = event.altKey
    ctrlKey = event.ctrlKey
   }  else {
    intKeyCode = window.event.keyCode;
    altKey = window.event.altKey;
    ctrlKey = window.event.ctrlKey
   }
  window.status=intKeyCode + ':'+ event.which +':'+altKey+ ':'+ctrlKey;

  if (((intKeyCode ==  93))) {
    // Ctrl-Menu
    openMenu(event,'popupcard',1);
    stopPropagation(event);
    
    return false;
  }
  return true;
}
function stopPropagation(event) {
  if (!event) event=window.event;
  if (event.stopPropagation) event.stopPropagation();
  else event.cancelBubble=true;
  if (event.preventDefault) event.preventDefault();
  else event.returnValue=true;  
}
// JScript source code
//Red : 0..255
//Green : 0..255
//Blue : 0..255
//Hue : 0,0..360,0<=>0..255
//Lum : 0,0..1,0<=>0..255
//Sat : 0,0..1,0<=>0..255

// o the object
// en the event name : mouseup, mouseodwn
function sendEvent(o,en) {  
  if (o) {
    if( document.createEvent ) {    
      //      var ne=document.createEvent("HTMLEvents");
      var ne;
      if ((en.indexOf('mouse') > -1)||(en.indexOf('click') > -1)) ne=document.createEvent("MouseEvents");
      else ne=document.createEvent("HTMLEvents");
      ne.initEvent(en,true,true);
      o.dispatchEvent(ne);
    }
    else {	
      try {
	o.fireEvent( "on"+en );
      }
      catch (ex) {
	;
      }

    } 
  }
}

//Retourne un tableau de 3 valeurs : H,S,L
function RGB2HSL (r, g, b)
{
  red = Math.round (r);
  green = Math.round (g);
  blue = Math.round (b);
  var minval = Math.min (red, Math.min (green, blue));
  var maxval = Math.max (red, Math.max (green, blue));
  var mdiff = maxval - minval + 0.0;
  var msum = maxval + minval + 0.0;
  var luminance = msum / 510.0;
  var saturation;
  var hue;
  if (maxval == minval)
  {
    saturation = 0.0;
    hue = 0.0;
  }
  else
  {
    var rnorm = (maxval - red) / mdiff;
    var gnorm = (maxval - green) / mdiff;
    var bnorm = (maxval - blue) / mdiff;
    saturation = (luminance <= 0.5) ? (mdiff / msum) : (mdiff / (510.0 - msum));
    if (red == maxval)
      hue = 60.0 * (6.0 + bnorm - gnorm);
    if (green == maxval)
      hue = 60.0 * (2.0 + rnorm - bnorm);
    if (blue == maxval)
      hue = 60.0 * (4.0 + gnorm - rnorm);
    if (hue > 360.0)
      hue -= 360.0;
  }
  return new Array (Math.round (hue * 255.0 / 360.0), Math.round (saturation * 255.0), Math.round (luminance * 255.0));
}

function ColorMagic (rm1, rm2, rh)
{
  var retval = rm1;
  if (rh > 360.0)
    rh -= 360.0;
  if (rh < 0.0)
    rh += 360.0;
  if (rh < 60.0)
    retval = rm1 + (rm2 - rm1) * rh / 60.0;
  else if (rh < 180.0)
    retval = rm2;
  else if (rh < 240.0)
    retval = rm1 + (rm2 - rm1) * (240.0 - rh) / 60.0;
  return Math.round (retval * 255);
}

//Retourne un tableau de 3 valeurs : R,G,B
function HSL2RGB (h, s, l)
{
  var hue = h * 360.0 / 255.0;
  var saturation = s / 255.0;
  var luminance = l / 255.0;
  var red;
  var green;
  var blue;
  if (saturation == 0.0)
  {
    red = green = blue = Math.round (luminance * 255.0);
  }
  else
  {
    var rm1;
    var rm2;
    if (luminance <= 0.5)
      rm2 = luminance + luminance * saturation;
    else
      rm2 = luminance + saturation - luminance * saturation;
    rm1 = 2.0 * luminance - rm2;
    red = ColorMagic (rm1, rm2, hue + 120.0);
    green = ColorMagic (rm1, rm2, hue);
    blue = ColorMagic (rm1, rm2, hue - 120.0);
  }
  return new Array (red, green, blue);
}

function getHSL(c) {
  var validcolor=false;
  var ot=document.getElementById('terriblecolor');
  if (c.substr(0,1) == "#") {
    r=parseInt('0x'+c.substr(1,2));
    g=parseInt('0x'+c.substr(3,2));
    b=parseInt('0x'+c.substr(5,2));
    validcolor=true;    
  } else {

    if (!ot) {
      ot=document.createElement('span');
      ot.id='terriblecolor';
    }
    ot.style.backgroundColor=c;
    rgb=getCssStyle(ot,'backgroundColor');
    if (rgb && rgb.substr(0,3)=="rgb") {  
      trgb=rgb.substr(4,rgb.length-5).split(',');
      r=parseInt(trgb[0]);
      g=parseInt(trgb[1]);
      b=parseInt(trgb[2]);
      validcolor=true;    
    }
  }


  if (validcolor) {
    return RGB2HSL (r, g, b);
  }
  return false;
}
function getAltern(c,ct,l) {

  var dhsl;

  hsl= getHSL(c);
  

  if (hsl) {
    if ((isNetscape) || (ct.substr(0,1) == "#")) {
      dhsl=getHSL(ct);
      if (dhsl[2] > 128) l=l-200; // dark color
    }
    // trgb=HSL2RGB (hsl[0],hsl[1], hsl[2]);
    trgb=HSL2RGB (hsl[0],hsl[1], l);
    for (i=0;i<3;i++) {
      if (trgb[i]>15)  trgb[i]=trgb[i].toString(16);
      else trgb[i]='0'+trgb[i].toString(16);
    }
    return('#'+trgb.join(''));
  }
  return '';
}

// altern color in a table between rows
function alterrow(tid,co,cot,by) {
  var c1=getAltern(co,cot,240);
  var c2=getAltern(co,cot,250);
  var c=[c1,c2];
  var t=document.getElementById(tid);
  if (t) {
    var ttr=t.getElementsByTagName('tr');  
    if (by) by2=2*by;
    
    for (var i=0;i<ttr.length;i++) {
      if (! ttr[i].style.backgroundColor) {
	if (!by) ttr[i].style.backgroundColor=c[(i%2)];
	else ttr[i].style.backgroundColor=c[parseInt((i % by2)/by)];
      }
    }
  }
}
// altern color in a table between rows
function opalterrow(tid,by) {
  var c=["url('Images/op10.png')","url('Images/op20.png')"];
  var t=document.getElementById(tid);
  if (t) {
    var ttr=t.getElementsByTagName('tr');  
    if (by) by2=2*by;

    if (isNetscape) {
      for (var i=0;i<ttr.length;i++) {
	if (!by) ttr[i].style.backgroundImage=c[(i%2)];
	else ttr[i].style.backgroundImage=c[parseInt((i % by2)/by)];
      }
    } else {
//       c=["Images/op10.png","Images/op20.png"];
//       for (var i=0;i<ttr.length;i++) {
// 	//img.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" +img.src+"',sizingMethod='scale') ";
// 	ttr[i].style.float='left';
// 	if (!by) ttr[i].style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(enable=true,src='" +c[(i%2)]+"',sizingMethod='scale') ";
// 	else ttr[i].style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" +c[parseInt((i % by2)/by)]+"',sizingMethod='scale') ";

	
      //   }
     


    }
  }
}

function alterfieldset(tid,co,cot,by) {
	if (!isNetscape) return; // not nice on IE
	var c1=getAltern(co,cot,240);
	var c2=getAltern(co,cot,250);
	var c=[c1,c2];
	var ci=0;
	var t=document.getElementById(tid);
	var tds;
	var field;
	if (t) {
		var ttr=getFieldSets(t);//t.getElementsByTagName('fieldset');  
		if (by) by2=2*by;
		for (var i=0;i<ttr.length;i++) {
			if (ttr[i].className=='frame') {
				field=null;
				if (ttr[i].tagName.toLowerCase() == 'fieldset') {
					field=ttr[i];
				} else {
					var dcs=ttr[i].getElementsByTagName('div');
					for (var j=0;j<dcs.length;j++) {
						if (dcs[j].className=='content') {
							field=dcs[j];
						}
					}
				}
				if (field) {
					if (!field.style.backgroundColor) {
						if (!by) field.style.backgroundColor=c[(ci%2)];
						else field.style.backgroundColor=c[parseInt((ci % by2)/by)];
					}
					tds=field.getElementsByTagName('td');
					//      alert(tds.lenght);
					for (var j=0;j<tds.length;j++) {
						if (!tds[j].style.backgroundColor) {
							//if (!by) tds[j].style.backgroundColor=c[(ci%2)];
							//else tds[j].style.backgroundColor=c[parseInt((ci % by2)/by)]; 
							tds[j].style.backgroundColor='transparent';

						}
					}
					ci++;
				}
			}
		}
	}
}



function addBookmark(url,title) {
       if ( isNetscape ){
           window.sidebar.addPanel(title,url,"");
       }
       else {
           window.external.AddFavorite(title,url);
       }
 }
var prevclass=false;
function showDiv(th,id) {
  var o=document.getElementById(id);
  var l;
  if (o) {
    if (!prevclass) prevclass=o.className;
    l=o.parentNode.childNodes;
    for (var i=0;i<l.length;i++) {
      if ((l[i].nodeName == o.nodeName)&&(l[i].className == o.className)) l[i].style.display='none';      
    }
    l=th.parentNode.childNodes;
    for (var i=0;i<l.length;i++) {
      if (l[i].nodeName == th.nodeName) l[i].className='';      
    }
    o.style.display='';    
    th.className='tabsel';
  }
}

function moveFieldset() {
  var ttr=getFieldSets(document);//document.getElementsByTagName('fieldset');
  var i,ln;
  var ltop=document.getElementById('toptab');
  var lf=new Array();
  for ( i=0;i<ttr.length;i++) {
    ln=ttr[i].getAttribute('name');
    if (ln) {
      lf.push(ttr[i].id);
    }
  }
  for ( i=0;i<lf.length;i++) {
    ln=document.getElementById(lf[i]);
    if (ln) {      
      ltop.parentNode.appendChild(ln);
    }
  }
  
}

function showFirstFieldset(event) {
  var elt,i;
  var to,ltr;
  to=document.getElementById('ttabs');
  if (to) {
    ltr=to.getElementsByTagName('span');
    if (ltr.length > 0) {
      ltr[0].onmousedown.apply(ltr[0],[event]);
      ltr[0].className='tabsel';
    }
  }
}

function showThisFieldset(event,tabid) {  
  var ltr;
 
  ltr=document.getElementById(tabid);    
  if (ltr) {  
    ltr.onmousedown.apply(ltr,[event]);
    ltr.className='tabsel';    
  }
}

function getFieldSets(o) {
	var tfs=o.getElementsByTagName('fieldset');
	var dfs=o.getElementsByTagName('div');
	var ttr=[];
	var i=0;
	for ( i=0;i<tfs.length;i++) {
		if (tfs[i].className=='frame') ttr.push(tfs[i]);
	}
	for ( i=0;i<dfs.length;i++) {
		if (dfs[i].className=='frame') ttr.push(dfs[i]);
	}
	return ttr;
}
// display element fieldset with this name
function showFieldset(event,o,n,docid,force) {

  var ttr=getFieldSets(document);//document.getElementsByTagName('fieldset');
  var btag=getElementsByNameTag(document,o.getAttribute('name'),'span');
  var ln,i;
  var prevtabname=o.id.substr(3);
  for ( i=0;i<btag.length;i++) {
    if (btag[i].className=='tabsel') prevtabname=btag[i].id.substr(3);
    $(btag[i]).removeClass('tabsel');
  }
  $(o).addClass('tabsel');
  var loaded=true;
  if (force) loaded=false;
  for ( i=0;i<ttr.length;i++) {
    ln=ttr[i].getAttribute('name');
    if (ln) {
      if (ln == n) {
    	  if (! force) loaded=(ttr[i].getAttribute('loaded') != "no");
    	  if (loaded) {
	        ttr[i].style.display='';

              $(ttr[i]).find(".hastipsy").each(function() {
                           $(this).tipsy('show');
                        });
    	  } else {
    		  ttr[i].parentNode.removeChild(ttr[i]);
    		  i--;
    	  }
      } else {
	    ttr[i].style.display='none';
          $(ttr[i]).find(".hastipsy").each(function() {
             $(this).tipsy('hide');
          });
      }
    } 
  } 
  if (docid) {
	  var needreload=true;
	  var tabname=o.id.substr(3);
	  if (! loaded) {
		  cible=document.createElement('div');
		  document.body.appendChild(cible);
		  requestUrlSend(cible,'?app=FDL&action=IMPCARD&zone=FDL:VIEWBODYCARD:S&id='+docid+'&onlytab='+tabname);
	  } else {
		  if (prevtabname && (prevtabname != tabname)) {
		  requestUrlSend(null,'?app=FDL&action=FDL_SETUSERTAG&tag=lasttab&id='+docid+'&value='+tabname);
		  }
	  }
  }
}


// correct getElementsByName for IE
function getElementsByNameTag(o,n,t) {
  var tt;
  if (isNetscape) return o.getElementsByName(n);

  
  var tt=o.getElementsByTagName(t);
  var lf=new Array();
  
  for (var i=0;i<tt.length;i++) {
    ln=tt[i].getAttribute('name');
    if (ln && (ln==n)) {
      lf.push(tt[i]);
    }
  }
  return lf;
  
}
function unsrolltable(o,f) {
  if (o) {
    o.className='';
    o.style.height='auto';
  }
  if (f) {
    f.style.display='none';
  }
}

// return tru if CR is pushed
function trackCR(event) {
  var intKeyCode;

  if (!event) event=window.event;
  intKeyCode=event.keyCode;
  if (intKeyCode == 13) return true;

  return false;
}

function htmlescape(str) {
    return str.replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;');
}