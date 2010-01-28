
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */


var Xpos = 0;
var Ypos = 0;
function GetXY(event) {
  if (window.event) {
    Xpos = window.event.clientX + document.documentElement.scrollLeft
                             + document.body.scrollLeft;
    Ypos = window.event.clientY + document.documentElement.scrollTop +
                             + document.body.scrollTop;
  }
  else {
    Xpos = event.clientX + window.scrollX;
    Ypos = event.clientY + window.scrollY;
  }    
}
function getFrameWidth(w) {
  if (! w) w=window;
      var winW = w.document.documentElement.offsetWidth;
      if (! winW) winW = w.innerWidth;
      if (! winW) winW = w.document.body.offsetWidth;

      return (winW);
  }

function getFrameHeight(w) {
  if (! w) w=window;
      var winH = winH=w.innerHeight;
     
      if (! winH)  w.document.documentElement.offsetHeight;
      if (! winH) winH = w.document.body.offsetHeight;
      return (winH);
  }

// get document object from iframe object
function getIdocument(ifr) {
if (ifr.contentDocument) {
    // For NS6
    return ifr.contentDocument; 
  } else if (ifr.contentWindow) {
    // For IE5.5 and IE6
    return ifr.contentWindow.document;
  } else if (ifr.document) {
    // For IE5
    return ifr.document;
  } else {
    return false;
  }
}

function CenterDiv(eid) { 
      var winH=getFrameHeight();
      var winW=getFrameWidth();

      if (document.getElementById) { // DOM3 = IE5, NS6
         var divlogo = document.getElementById(eid);
	 divlogo.style.display = 'inline';
     
         if ((winH>0) && (winW>0)) {
	   
           divlogo.style.top = (winH/2 - (divlogo.offsetHeight/2)+ document.body.scrollTop)+'px';
	   divlogo.style.left = (winW/2 - (divlogo.offsetWidth/2))+'px';

         }
    
       }
    return true;
}

function getKeyPress(event)
{
  var intKeyCode;

  if (window.event) {
    intKeyCode = window.event.keyCode;
    alert(window.event.type);
  } else {
    intKeyCode = event.which;
    alert(event.type);
  }
  alert('key:'+intKeyCode);
  return intKeyCode;
}


function autoVresize() {
  if (window != top) return;

  var dw=0;
  var dh=0;
  var sh;
  var ih;

  if (isIE) {
    sh=document.body.scrollHeight+8;
    ih=document.documentElement.offsetHeight;
  } else {
    sh=document.body.scrollHeight;    
    ih=getFrameHeight()-4;
  }

  availHeight=self.screen.availHeight-300;
 
  if (sh > availHeight) 
    dh=availHeight-ih;  
  else 
    dh=sh-ih;

 
  //    alert('V['+sh+']['+ih+']['+dh+',SH['+document.body.scrollHeight);
  if (dh > 0) window.resizeBy(dw,dh);

}

function autoHresize1() {
  if (window != top) return;
  var dw=0;
  var dh=0;
  var sw;
  var iw;

  if (isIE) {
    sw=document.body.clientWidth+4;
    if (isIE7) sw=document.documentElement.scrollWidth;
    //sw=document.body.scrollWidth;
    iw=document.documentElement.offsetWidth;
  } else {
    sw=document.documentElement.scrollWidth-4;
    iw=getFrameWidth()-4;
  }
  if (document.body.scrollWidth > self.screen.availWidth) 
    dw=self.screen.availWidth-document.body.clientWidth;
  else {    
    dw=sw-iw;
  }

  //alert('H1['+document.body.clientWidth+']['+document.documentElement.offsetWidth+']['+document.body.offsetWidth+']['+document.documentElement.scrollWidth);
  //alert('H['+sw+']['+iw+']['+dw);
  if (dw > 0) {
    if (isIE) dw+=22; // add scrollbar
    window.resizeBy(dw,dh);
  }
}
function autoHresize() {  
  autoHresize1();
  autoHresize1(); // need twice when very empty ?!
}

function autoWresize() {
  autoHresize();
  autoVresize();
}
