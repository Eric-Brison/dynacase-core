function lauch_action(img, appname, descr) {

  if (parent && parent.body) parent.body.location.replace('[CORE_BASEURL]app='+appname);
  else subwindow([FDL_VD2SIZE],[FDL_HD2SIZE],'wbody'+appname,'[CORE_BASEURL]app='+appname);

  top.document.title="WHAT: "+descr;

  document.imgapp.src=img

  etitle = document.getElementById('apptitle2');
  if (etitle) {
    //    alert(etitle.innerHTML);
    


    etitle.innerHTML = descr;
  }


}
