
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
