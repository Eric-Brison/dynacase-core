function displayLogMsg(logmsg) {
  if (top.foot) {
    var log=top.foot.document.getElementById('slog');
    if (log) {
      var classn = 'CORETblCell'
  
      
      if ((log.options) && (log.options.length > 0)) {
	if (log.options[log.options.length-1].className == "CORETblCell") 
	  classn='CORETblCellAltern';
        var k=log.options.length;
      } else {
        var k=0;	
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
}
