

function subwindow(h, w, name, url) {
   var screen_width, screen_height;
   var win_top, win_left;
   var HelpWin;
   screen_height        = 0;     screen_width      = 0;
   win_top              = 0;     win_left          = 0;
   if (window.innerWidth) screen_width = window.innerWidth;
   if (window.innerHeight) screen_height = window.innerHeight;
   win_top  = screen_height - h - 20;
   win_left = screen_width  - w  - 20;
   me = windowExist(name, true);
  
   if (! me) {
     me  = window.open(
		       url,
		       name,
		       'resizable=yes,scrollbars=yes,width='+w+',height='+h+',top='+win_top+',left='+win_left);
    getConnexeWindows(me);
   } else {
     me.location.href=url;
   }
  me.focus();

    
  return me;
}

// with menu
function subwindowm(h, w, name, url) {

   var screen_width, screen_height;
   var win_top, win_left;
   var HelpWin;
   screen_height        = 0;     screen_width      = 0;
   win_top              = 0;     win_left          = 0;
   if (window.innerWidth) screen_width = window.innerWidth;
   if (window.innerHeight) screen_height = window.innerHeight;
   win_top  = screen_height - h - 20;
   win_left = screen_width  - w  - 20;
   me = windowExist(name, true);
   if (! me) {
     me  = window.open(
		       url,
		       name,
		       'menubar=yes,resizable=yes,scrollbars=yes,width='+w+',height='+h+',top='+win_top+',left='+win_left);
     getConnexeWindows(me);
   } else {
     me.location.href=url;
   }
  me.focus();

    
  return me;
}
