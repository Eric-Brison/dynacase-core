function lauch_action(img, appname, descr) {

  parent.body.location.replace('[CORE_BASEURL]app='+appname);

  top.document.title="WHAT: "+descr;

  document.imgapp.src=img

  etitle = document.getElementById('apptitle2');
  if (etitle) {
    //    alert(etitle.innerHTML);
    


    etitle.innerHTML = descr;
  }


}
