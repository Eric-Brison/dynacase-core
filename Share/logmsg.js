function displayLogMsg(logmsg) {

  if (logmsg.length == 0) return;
  
  var log=false;
  if (top.foot) {
    log=top.foot.document.getElementById('slog');
  } else {
    // redirect to foot function
      if (window .name != "foot") {
	var wfoot = window.open('','foot','');
	wfoot.displayLogMsg(logmsg);
      }
    return;
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
      log.options[k]=new Option(logmsg[i],'',false,false);
      log.options[k].className=classn;
      k=log.options.length;
    }
    log.selectedIndex=log.options.length-1;
    
    if ((! log.options) || (log.options.length == 0)) log.style.display='none';
    else log.style.display='inline';
  }
  
  
}

