
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */


function getImageWidth(img) {
  var w=0;
  w=img.style.width;
  if (!w) w=img.offsetWidth;

  if (!w) w=img.width;

  w=parseInt(w);
  return w;
}

function resizeImages() {
  if(/MSIE (5|6)/.test(navigator.userAgent)) return;
  var sfEls1 = document.getElementsByTagName("IMG");
  var is,w;
  var c=0;
  //	alert(sfEls1.length);
  for (var i=0; i<sfEls1.length; i++) {
    is= sfEls1[i].getAttribute('needresize');
    if (is) {
      //sfEls1[i].style.border='green solid 1px';
      w=getImageWidth( sfEls1[i]);
	    
      if (w > 0) {
	if ((sfEls1[i].src.indexOf('&') != -1) && (sfEls1[i].src.indexOf('geticon') == -1)) return;
	sfEls1[i].src='resizeimg.php?size='+w+'&img='+sfEls1[i].src;
	sfEls1[i].removeAttribute('needresize');
	c++;
      } else {
	//displayPropertyNames(sfEls1[i]);
	      
	//sfEls1[i].style.border='red solid 2px';
	      
      }
    }
  }
}
if(!/MSIE (5|6)/.test(navigator.userAgent)) {
  addEvent(window,"load",resizeImages);
 }

