function lauch_action(event, img, appname, descr) {
  if (!event) event=window.event;
  ctrlKey = event.ctrlKey;
  
  if (ctrlKey) {
    if (event.stopPropagation) event.stopPropagation();
    else event.cancelBubble=true;
    if (event.preventDefault) event.preventDefault();
    else event.returnValue=true;

  
    
  }

  if (parent && parent.body && (!ctrlKey)) parent.body.location.replace('[CORE_BASEURL]app='+appname);
  else if (this.fbody && (!ctrlKey)) this.fbody.location.replace('[CORE_BASEURL]app='+appname);
  
  else subwindow([FDL_VD2SIZE],[FDL_HD2SIZE],'wbody'+appname,'[CORE_BASEURL]app='+appname);

  top.document.title=descr;


  var limg=document.getElementById('imgapp');
  if (limg) {
    if (limg.src) limg.src=img;
    
      if (!isNetscape) {
	limg.style.filter='';
	correctOnePNG(limg);
      }
      
    
  }
  etitle = document.getElementById('apptitle2');
  if (etitle) {
    //    alert(etitle.innerHTML);
    


    etitle.innerHTML = descr;
  }


}
