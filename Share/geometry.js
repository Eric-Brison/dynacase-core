
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
function getFrameWidth() {
      winW = window.innerWidth;
      if (! winW)	   
            winW = document.body.offsetWidth;

      return (winW);
  }

function getFrameHeight() {
      winH= window.innerHeight;
      if (! winH)	
           winH = document.body.offsetHeight;
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

  if (document.body.scrollHeight > self.screen.availHeight) 
    dh=self.screen.availHeight-document.body.clientHeight;
  else 
    dh=document.body.scrollHeight-document.body.clientHeight;

  dw=0;
  if (dh > 0) window.resizeBy(dw,dh);
}


