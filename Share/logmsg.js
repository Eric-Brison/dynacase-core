function displayLogMsg(logmsg) {
  var log=false;
  if (top.foot) {
    log=top.foot.document.getElementById('slog');
  } else {
    wfoot = window.open('','foot','');
    log=wfoot.document.getElementById('slog');
  }
    if (log) {
      var classn = 'CORETblCell'        
      var k=0;

      if ((log.options) && (log.options.length > 0)) {
	if (log.options[log.options.length-1].className == "CORETblCell") 
	  classn='CORETblCellAltern';
        k=log.options.length;
      } 
      for (var i=0;i<logmsg.length;i++) {
        log.options[k]=new Option(logmsg[i]);
        log.options[k].className=classn;
	k=log.options.length;
      }
      log.selectedIndex=log.options.length-1;
   
      if ((! log.options) || (log.options.length == 0)) log.style.display='none';
      else log.style.display='inline';
    }
    
  
}
