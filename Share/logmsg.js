
var windows= new Object();;



function displayPropertyNames(obj) {
  var names="";
  for (var name in obj) {
   try {
     names += name +" - " + obj[name] + "][";
   }
   catch (ex) {
     names += name +" - " + "unreadable" + "][";
   }
  }
  alert(names);
}
function getConnexeWindows(w) {
  var cw;

  cw=getChildFrames(w.top);

  
   if (w.top.opener) cw=cw+getConnexeWindows(w.top.opener);

  return cw;
}
function getChildFrames(w) {
  
  var fnames='';
  var fname='';

  try {
      fname=w.frames;      
  }
  catch (ex) {
    return fnames;
  }
  for (var i=0;i<w.frames.length;i++) {
    try {
      fname=w.frames[i].name;      
    }
    catch (ex) {
      fname='';
    }
    if (fname && (fname != '')) windows[fname]=w.frames[i];
    fnames = fnames + ' ' +i + fname;
 
    fnames = fnames+getChildFrames(w.frames[i]);
    
  }
  
  return fnames;
}

function windowExist(Name, NoOpen) {
  var dy=self.screen.availHeight;
  var dx=self.screen.availWidth;
 
  getChildFrames(window);

  if (windows[Name]  ) {
    
    if ( windows[Name]=='none') return false;

    if (windows[Name].closed) return false;
    
    try {
      var w=windows[Name].document;
    }
    catch (ex) {
      windows[Name]='none';
      return false;
    }
    return  windows[Name];
  }

  getConnexeWindows(window);
  if (windows[Name]) return  windows[Name];

  // ---------------------
  // Try open
  if (NoOpen == '') {
    var w=window.open('',Name,'top='+dy+',left='+dx+'menubar=no,resizable=no,scrollbars=no,width=1,height=1');
    if (w.opener && (w.opener.location.href == self.location.href) && (w.location.href=='about:blank')) {
      w.close();
      windows[Name]='none';
      return false;
    }
    windows[Name]=w;
    getConnexeWindows(w);
  }
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

