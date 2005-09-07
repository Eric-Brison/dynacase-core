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
  else subwindow([FDL_VD2SIZE],[FDL_HD2SIZE],'wbody'+appname,'[CORE_BASEURL]app='+appname);

  top.document.title="FREEDOM - "+descr;

  document.imgapp.src=img

  etitle = document.getElementById('apptitle2');
  if (etitle) {
    //    alert(etitle.innerHTML);
    


    etitle.innerHTML = descr;
  }


}
