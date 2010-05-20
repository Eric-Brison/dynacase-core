
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

function mcalAddEvent(o, e, f, s) {
  if (o.addEventListener){ o.addEventListener(e,f,s); return true;   } 
  else if (o.attachEvent) { return o.attachEvent("on"+e,f); } 
  else { return false; }
}
function mcalDelEvent(o,e,f,s){
        if (o.removeEventListener){ o.removeEventListener(e,f,s); return true; }
        else if (o.detachEvent){ return o.detachEvent("on"+e,f); }
        else { return false; }
}  

function mcalCancelEvent(e) {
  if (!e) e = window.event;
  if (e.stopPropagation) e.stopPropagation();
  else e.cancelBubble = true;
}

function mcalEventXY(event) {
  var c = { x:0, y:0 };
  if (window.event) {
    c.x = window.event.clientX + document.documentElement.scrollLeft
                             + document.body.scrollLeft;
    c.y = window.event.clientY + document.documentElement.scrollTop +
                             + document.body.scrollTop;
  }
  else {
    c.x = event.clientX + window.scrollX;
    c.y = event.clientY + window.scrollY;
  } 
  return c;
}

mcalSetDisplay = function(eid, x, y, w, h, z) {
  if (document.getElementById(eid)) {
    var elt = document.getElementById(eid)
    with (elt) {
      style.position = 'absolute';
      style.left = x;
      style.top = y;
      style.width = w;
      style.height = h;
      style.display = '';
    }
  }
}

mcalDrawRectAbsolute = function(id, father, x, y, w, h, z, c, v, t, oAttr, oStyle) {

  var fat;
  var rect = null;

  if (father!='') {
    fat = document.getElementById(father);
  } else {
    fat = document.getElementsByTagName("body")[0];
  }
  rect = document.getElementById(id); 
  if (rect!=null) {
    existElt = true;
  } else {
    rect = document.createElement('div');
    rect.id = id;
    rect.name = id;
    existElt = false;
  }
  
  with (rect) { 
    if (t!='') innerHTML = t;
    style.position = 'absolute';
    style.background = c;
    style.left = x;
    style.top = y;
    style.width = w;
    style.height = h;
    style.zIndex = z;
    if (c!='') className = c;
    style.display = (v?'':'none');
  }
  
  msg ='';
  if (oAttr) {
    for (ix=0; ix<oAttr.length; ix++) {
      rect.setAttribute(oAttr[ix].id, oAttr[ix].val);
    }
  }
  if (oStyle) {
    for (ix=0; ix<oStyle.length; ix++) {
      rect.style.setProperty(oStyle[ix].id, oStyle[ix].val, "" );
    }
  }
  
  fat.appendChild(rect);
  return true; 
}



mcalGetZoneCoord = function(z) {
  if (!document.getElementById(z)) {
    mcalShowError('GetZoneCoord: Element '+z+' not found');
    return;
  }
  var coord = getAnchorPosition(z);
  coord.w = getObjectWidth(document.getElementById(z));
  coord.h = getObjectHeight(document.getElementById(z));
  return coord;
}

mcalShowMessage = function(m) { mcalShow(m, 'blue', 'white', '__mcalinfo'); };
mcalHideMessage = function() { document.getElementById('__mcalinfo').style.display='none'; };

mcalShowError = function(m) { mcalShow(m, 'red', 'white', '__mcalerror'); }
mcalShowTrace = function(m) { mcalShow(m, 'yellow', 'black', '__mcaltrace'); }

mcalShow = function(m,fg,bg,elt) {
  var attr = [ ];
//     { id:'onclick', val:'this.style.display=\'none\'' },
//   ];
  var style = [
     { id:'font-size', val:'11' },
     { id:'overflow', val:'auto' },
     { id:'margin', val:'3em' },
     { id:'padding', val:'3em' },
     { id:'border', val:'3px ridge '+fg },
     { id:'background-color', val:bg },
     { id:'color', val:fg },
  ];
  var ct = '<div onclick="this.parentNode.style.display=\'none\'" style="margin:3px; background-color:'+fg+'; color:'+bg+'; font-weight:bold; width:95%">Click to Close</div>'+m;
  mcalDrawRectAbsolute(elt, '', 0, 0, 500, 'auto', 10000, m, true, ct, attr, style);
}
    

mcalParseReq = function(rstr, params, vals) {
  var text = rstr;
  if (params.length != vals.length) return rstr;
  var ret = rstr;
  for (var ip=0; ip<params.length; ip++) {
    var  rgexp= new RegExp('%'+params[ip]+'%', 'g'); 
    ret = ret.replace(rgexp, vals[ip]);
  }
  return ret;
}

  mcalDateS = function(d) {
    return d.toLocaleDateString()+' '+d.toLocaleTimeString(); 
  }
  mcalDateTs = function(ts) {
    d = new Date(ts); 
    return mcalDateS(d); 
  }
