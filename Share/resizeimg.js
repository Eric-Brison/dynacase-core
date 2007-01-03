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


function getImageWidth(img) {
  var w=0;
  w=img.style.width;
  if (!w) w=img.offsetWidth;

  if (!w) w=getCssStyle(img,'width');
  if (!w) w=img.width;

  w=parseInt(w);
  return w;
}

function resizeImages() {
	var sfEls1 = document.getElementsByTagName("IMG");
	var is,w;
	//	alert(sfEls1.length);
	for (var i=0; i<sfEls1.length; i++) {
	  is= sfEls1[i].getAttribute('needresize');
	  if (is) {
	    //sfEls1[i].style.border='green solid 1px';
	    w=getImageWidth( sfEls1[i]);
	    
	    if (w > 0) {
	      sfEls1[i].src='resizeimg.php?size='+w+'&img='+sfEls1[i].src;
	      sfEls1[i].removeAttribute('needresize');
	    } else {
	      displayPropertyNames(sfEls1[i]);
	      sfEls1[i].style.border='red solid 2px';
	      
	    }
	  }
	}
	
}
addEvent(window,"load",resizeImages);
