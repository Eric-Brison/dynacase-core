
function windowExist(Name) {
  var dy=self.screen.availHeight;
  var dx=self.screen.availWidth;
 

  
  if (window[Name]  ) {
    
    if ( window[Name]=='none') return false;
    if (window[Name].closed) return false;
    else return  window[Name];
  }

  var w=window.open('',Name,'top='+dy+',left='+dx+'menubar=no,resizable=no,scrollbars=no,width=1,height=1');
  if (w.opener && (w.opener.location.href == self.location.href) && (w.location.href=='about:blank')) {
    w.close();
    window[Name]='none';
    return false;
  }
  window[Name]=w;
  return w;
}
function displayLogMsg(logmsg) {

  if (logmsg.length == 0) return;
  
  var log=false;
  if (top.foot) {
    log=top.foot.document.getElementById('slog');
  } else {
    // redirect to foot function
      if (window.name != "foot") {
	var wfoot = windowExist('foot');
	if (wfoot)  wfoot.displayLogMsg(logmsg);
      }
    return;
  }
  
  if (log) {
    var classn = 'CORETblCell';    
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
    
    logi=top.foot.document.getElementById('ilog');
    if ((! log.options) || (log.options.length == 0)) logi.style.display='none';
    else logi.style.display='inline';
  }
  
  
}

