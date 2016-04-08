
/**
 * @author Anakeen
 */

include_js('WHAT/Layout/AnchorPosition.js');
include_js('FDL/Layout/common.js');
include_js('WHAT/Layout/DHTMLapi.js');
include_js('WHAT/Layout/AnchorPosition.js');
include_js('WHAT/Layout/geometry.js');
include_js('FDL/Layout/iframe.js');

function displayWindow(height, width, ref, title, x, y, id, backgroundcolor) {
    var dialogFrame = $("#"+id+"_s");
    var oldFrame = $("#oldFrame_"+id);
    if (dialogFrame.length <= 0) {
        dialogFrame = $('<iframe id="'+id+'_s" style="padding: 0" frameborder="0"  allowtransparency="yes"></iframe>').appendTo('body');
    } else {
         if (dialogFrame.dialog("isOpen") == true) {
             var position = dialogFrame.dialog("option", "position");
             x = position[0];
             y = position[1];
             height = dialogFrame.dialog("option", "height");
             width = dialogFrame.dialog("option", "width");
         } else {
             x = parseFloat(oldFrame.attr("data-posX"));
             y = parseFloat(oldFrame.attr("data-posY"));
             height = oldFrame.height();
             width = oldFrame.width();
         }
        dialogFrame.attr("src", ref);
    }

   dialogFrame.dialog({
        autoOpen:true,
        modal:false,
        draggable:true,
        resizable:true,
        height:height,
        width:width,
        title: title,
        open:function (event, ui) {
            if (isIE) {
                $('body').css('overflow', 'hidden');
            }
        },
        position:[x, y],
        beforeClose:function () {
            $(this).attr("src", "Images/1x1.gif");
            return false;
        },
        dragStop: function(event, ui) {
            if (id == "POPDOC") {
                var newParam = parseInt(ui.position.left) +"+"+parseInt(ui.position.top)+"+"+parseInt($(this).width())+"x"+parseInt($(this).height());
                setparamu("FDL", "MVIEW_GEO", newParam);
            }
        },
        resizeStop: function(event, ui) {
            if (id == "POPDOC") {
                var newParam =parseInt(ui.position.left)+"+"+parseInt(ui.position.top)+"+"+parseInt(ui.size.width)+"x"+parseInt(ui.size.height);
                setparamu("FDL", "MVIEW_GEO", newParam);
            }


        }
    }).bind('dialogdragstart dialogresizestart', function(event, ui) {

           var overlay = $(this).find('.hidden-dialog-overlay');
           if (!overlay.length) {
               overlay = $('<div class="hidden-dialog-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;z-order:100000;"></div>');
               overlay.appendTo(this.parentNode);
               if (isIE6) {
                   overlay.css("height","1000px").css("width","100%");
               }
           }
           else {
               overlay.show();
           }
           if (event.type=="dialogdragstart") {
               overlay.css("cursor", "move");
           } else if (event.type=="dialogresizestart") {
               overlay.css("cursor", "nw-resize");
           }
       }).bind('dialogdragstop dialogresizestop', function() {
           $(this.parentNode).find('.hidden-dialog-overlay').hide();
       });
    dialogFrame.width(width).height(height);

    dialogFrame.attr("src", ref);
    if (backgroundcolor) {
        dialogFrame.css("background-color", backgroundcolor);
        dialogFrame.parent().find(".ui-dialog-titlebar").removeClass("ui-widget-header").css("background-color", backgroundcolor);
    }

    dialogFrame.on("load", function () {
            var $this = $(this), doc, oldFrame, position;
            doc = this.contentDocument || this.contentWindow.document;
            if (doc) {
                if (title !== false) {
                    dialogFrame.dialog("option", "title", $("<div/>").text((doc.title || title)).html());
                }
                if (backgroundcolor &&  doc.getElementById("documentBody")) {
                    doc.getElementById("documentBody").style.backgroundColor =  backgroundcolor;
                }
                if (isIE6) {
                    $(doc).find('body').css("margin-right","30px");
                    $(doc).find('html').css("overflow-x", "hidden");
                }
            }
            if (doc && doc.location && doc.location.href &&
                doc.location.href.toLowerCase().indexOf("images/1x1.gif") > -1) {
                oldFrame = $("#oldFrame_"+id);
                if (oldFrame.length === 0) {
                    oldFrame = $('<div id="oldFrame_'+id+'" style="display : none;"></div>');
                    $("body").append(oldFrame);
                }
                oldFrame.empty();
                if (dialogFrame.dialog("isOpen") == true) {
                    position = dialogFrame.dialog("option", "position");
                    oldFrame.height(dialogFrame.dialog("option", "height"));
                    oldFrame.width(dialogFrame.dialog("option", "width"));
                    oldFrame.attr({
                        "data-posX": position[0],
                        "data-posY": position[1]
                    });
                    var onclick = $("#onclicksave").attr("data-onclick");
                    var $header = $(".header");
                    if (onclick) {
                        var links = $header.find(".barmenu").find("a");
                        links.each(function() {
                            if ($(this).css("visibility") == "visible") {
                                this.onclick = new Function(onclick);
                            } else {
                                this.style.visibility='visible';
                            }
                        });
                        $header.css("cursor", "auto");
                        $("#onclicksave").attr("data-onclick", "");
                    }
                    if (isIE) {
                        $header.css("filter", '');
                        $this.dialog("close");
                    } else {
                        $header.css('opacity', 1.0);
                    }
                }
                if (isIE) {
                    $('body').css('overflow', 'auto');

                }
                oldFrame.append($this);
            }
        });
}
function popdoc(event,url,title) {
  if (event) event.cancelBubble=true;
    if (ctrlPushed(event)) {
        window.open(
            url,
            '_blank',
            'resizable=yes,scrollbars=yes,width='+[FDL_VD2SIZE]+',height='+[FDL_HD2SIZE]);
    } else {
        var scrolly=window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
        if (! title) title='';
        var mviewgeo = $("meta[name=document-mviewgeo]").attr("content");
        var x = [mgeox],
            y = [mgeoy],
            h = [mgeoh],
            w = [mgeow];
        if (mviewgeo != undefined) {
            var match = mviewgeo.match(/([0-9]+)\+([0-9]+)\+([0-9]+)x([0-9]+)/);
            if (match && match.length >4) {
                x = parseInt(match[1]);
                y = parseInt(match[2]);
                w = parseInt(match[3]);
                h = parseInt(match[4]);
            }
        }
        displayWindow(h, w, url, title, x, y +scrolly, 'POPDOC');
    }
}
function poptext(text,title) {

    var dpopdoc = document.getElementById('POPDOC_s');
	var fpopdoc;
	var scrolly=window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
	if (! dpopdoc) {
		new popUp([mgeox], [mgeoy] + scrolly, [mgeow], [mgeoh], 'POPDOC', text, 'white', '#00385c', '16pt serif', title, '[COLOR_B5]', '[CORE_TEXTBGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', '[CORE_BGCOLORALTERN]', true, true, true, true, false, false);
	} else {      
		if ((getObjectTop(dpopdoc) < scrolly) || 
				(getObjectTop(dpopdoc) > (getInsideWindowHeight() + scrolly))	){
			// popup is not visible in scrolled window => move to visible part
			movePopup('POPDOC' ,[mgeox], [mgeoy]+scrolly);
		} 
		changecontent( 'POPDOC' , url );
		showbox( 'POPDOC');
	}
}

// create popup for insert div after
function newPopdiv(event,divtitle,x,y,w,h) {

  if (event) event.cancelBubble=true;     
    
    GetXY(event); 
  if (!x) x=Xpos;
  if (!y) y=Ypos;
  if (!w) w=[mgeow];
  if (!h) h=[mgeoh];

    var dpopdiv = document.getElementById('POPDIV_s');
    var fpopdiv;
    var scrolly=window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
    if (! dpopdiv) {
      new popUp(x, y, w, h, 'POPDIV', 'zou', "[CORE_BGCOLOR]", '[CORE_TEXTFGCOLOR]', '16pt serif', divtitle, '[COLOR_B5]', '[CORE_TEXTFGCOLOR]', '[COLOR_B7]', "[CORE_BGCOLORALTERN]", 'black', true, true, true, true, false, false,true);
    
    } else {
      if ((getObjectTop(dpopdiv) < scrolly) || 
	  (getObjectTop(dpopdiv) > (getInsideWindowHeight() +scrolly))	){
	// popup is not visible in scrolled window => move to visible part
	movePopup('POPDIV' ,[mgeox], [mgeoy]+scrolly);
      } 
      showbox( 'POPDIV');    
  }
    return document.getElementById('POPDIV_c');
}


function postit(url,x,y,w,h) {
		      
  if (!x) x=150;
  if (!y) y=110;
  if (!w) w=300;
  if (!h) h=200;
    displayWindow(h, w, url, false, x, y, 'POSTIT', "#FF6");
}


function viewwask(url,x,y,w,h) {
		      
  if (!x) x=180;
  if (!y) y=210;
  if (!w) w=300;
  if (!h) h=200;
  var dviewwask = document.getElementById('VIEWWASK_s');
  if (! dviewwask) {
    new popUp(x, y, w, h, 'VIEWWASK', url, '[COLOR_WHITE]', '#00385c', '16pt serif', '[TEXT:ask]', '[COLOR_B5]', '[CORE_TEXTBGCOLOR]', '[COLOR_B7]', 'transparent', '[CORE_BGCOLORALTERN]', true, true, true, true, true, false,true);
    
  } else {
    if ((getObjectTop(dviewwask) < document.body.scrollTop) || 
	(getObjectTop(dviewwask) > (getInsideWindowHeight() +document.body.scrollTop))	){
      // popup is not visible in scrolled window => move to visible part
      movePopup('VIEWWASK' ,250, 210+document.body.scrollTop);
    } 
    changecontent( 'VIEWWASK' , url );
    showbox( 'VIEWWASK');
  }
}
function centerError() {
  CenterDiv('error');
}
function reloadWindow(w) {
    if (w && w.location) {
  var h=w.location.href;

  var l=h.substring(h.length-1);
  if (l=='#') h=h.substring(0,h.length-1);
  w.location.href=h;
    }

  
}
function refreshParentWindows(famid) {
    var pWindow=getParentWindow();

    if (pWindow) {
        if (pWindow.flist) reloadWindow(pWindow.flist);
        else if (pWindow.fvfolder) reloadWindow(pWindow.fvfolder);
        else if (pWindow.ffoliolist) {
            reloadWindow(pWindow.ffoliolist);
            if (pWindow.ffoliotab) reloadWindow(pWindow.ffoliotab);
        } else if (window.opener && window.opener.document.needreload) reloadWindow(window.opener);

        if (famid) {
            if (pWindow['if_' + famid]) reloadWindow(pWindow['if_' + famid]);
        }
    }
}
function updatePopDocTitle() {
    var pWindow=getParentWindow();
  if (pWindow && window.name) {
       
      var l=window.name.substring(0,window.name.length - 5)+'_ti';      
    var fpopdoc_t= pWindow.document.getElementById(l);
    if (fpopdoc_t) {
      if (window.document && (window.document.title!="")) {
	fpopdoc_t.innerHTML=htmlescape(window.document.title);
      } else {
	fpopdoc_t.innerHTML="mini vue";
      }
    }
  }
  /*
   * Preload vewwait's throbber
   */
  _preloadWIMG();
}

function viewwaitbarmenu(thea,bar,title) {
    if (bar && thea) {
        var dialogs = $(".ui-dialog");
        var open = false;
        dialogs.each(function() {
            if ($(this).dialog("isOpen")) {
                open = true;
                $(this).dialog("close");
            }
        });
        var onclick = $(thea).attr('onclick');
        var la=bar.getElementsByTagName('a');
        for (var i=0;i<la.length;i++) {
            la[i].style.visibility='hidden';
        }
        thea.onclick='';
        thea.style.visibility='visible';
        if (!open) {
            globalcursor('wait');
            setTimeout('viewwait(true)',500);
            setbodyopacity(0.5);
        } else {
            var $header = $(".header");
            $header.append('<span style="visibility: hidden;" data-onclick="'+onclick+'" id="onclicksave"></span>');
            $header.css("cursor", "wait");
            if (isIE) {
                $header.css("filter", 'alpha(opacity50)');
            } else {
                $header.css('opacity', 0.5);
            }
        }
    }
}
// op 
function resetbodyopacity() {
  // alert(document.body);
  if (isIE) {
     document.body.style.filter='';
  } else {
     document.body.style.opacity=1.0;
  }
}


// op between 0..1.0
function setbodyopacity(op) {
  if (isIE) {
    op=parseInt(op*100);
     document.body.style.filter='alpha(opacity='+ op + ')';
  } else {
     document.body.style.opacity=op;
  }
}
function viewwait(view) {
  var wimgo = document.getElementById('WIMG');
  if (! wimgo) {
    wimgo = document.createElement('img');
    wimgo.setAttribute('src','Images/loading.gif');
    wimgo.setAttribute('id','WIMG');
    wimgo.style.display='none';
    wimgo.style.position='absolute';
    wimgo.style.backgroundColor='#FFFFFF';
    wimgo.style.border='groove black 2px';
    wimgo.style.padding='4px';
    wimgo.style.MozBorderRadius='4px';
    document.body.appendChild(wimgo);
  }
  if (wimgo) {
    if (view) {
      CenterDiv(wimgo.id);
      wimgo.style.display='inline';
    } else {
      wimgo.style.display='none';
    }
  }
}
/**
 * Pre-create a hidden throbber element to allow the throbber's image
 * to be pre-loaded before the first use.
 *
 * @private
 */
function _preloadWIMG() {
    viewwait(false);
}
addEvent(window,"load",updatePopDocTitle);
