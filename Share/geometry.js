
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
      winW = w.innerWidth;
      if (! winW)	   
            winW = w.document.body.offsetWidth;

      return (winW);
  }

function getFrameHeight(w) {
  if (! w) w=window;
      winH= w.innerHeight;
      if (! winH)	
           winH = w.document.body.offsetHeight;
      return (winH);
  }



function CenterDiv(eid) { 
      var winH=getFrameHeight();
      var winW=getFrameWidth();


      if (document.getElementById) { // DOM3 = IE5, NS6
         var divlogo = document.getElementById(eid);
	 divlogo.style.display = 'inline';

     
         if ((winH>0) && (winW>0)) {
	   
           divlogo.style.top = winH/2 - (divlogo.offsetHeight/2)+ document.body.scrollTop;
           divlogo.style.left = winW/2 - (divlogo.offsetWidth/2);

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


function autoHresize() {
  if (window != top) return;

  var dw=0;
  var dh=0;
  availHeight=self.screen.availHeight-300;

  if (document.body.scrollHeight > availHeight) 
    dh=availHeight-document.body.clientHeight;
  else 
    dh=document.body.scrollHeight-document.body.clientHeight;

  if (dh > 0) {
    window.resizeBy(dw,dh);

    // double resize for mozilla ?
    if (document.body.scrollHeight < availHeight) {
      if (document.body.scrollHeight > document.body.clientHeight) {
    
	dh=document.body.scrollHeight-document.body.clientHeight;
	if (dh > 0) window.resizeBy(dw,dh);
      }
    }
  }
}

function autoVresize() {
  if (window != top) return;

  var dw=0;
  var dh=0;
  if (document.body.scrollWidth > self.screen.availWidth) 
    dw=self.screen.availWidth-document.body.clientWidth;
  else 
    dw=document.body.scrollWidth-document.body.clientWidth;

  if (dw > 0) window.resizeBy(dw,dh);
}

function autoWresize() {
  autoVresize();
  autoHresize();
}
