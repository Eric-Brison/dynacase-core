function displayLogMsg(logmsg) {
  if (top.foot) {
    var log=top.foot.document.getElementById('slog');
    if (log) {
      var classn = 'CORETblCell'
      if (log.options.length > 0)
	if (log.options[log.options.length-1].className == "CORETblCell") 
	  classn='CORETblCellAltern';
      for (var i=0;i<logmsg.length;i++) {
        var k=log.options.length;
        log.options[k]=new Option(logmsg[i]);
        log.options[k].className=classn;
	
      }
      log.selectedIndex=log.options.length-1;
    }
  }
}
